<?php

use UzerpPhinx\UzerpMigration;

class UserPasswordHashUpgrade extends UzerpMigration
{
    // Cache keys to be cleaned on migration
    protected $cache_keys = array(
        '[table_fields][useroverview]',
        '[table_fields][hasrolesoverview]'
    );



    public function down()
    {
        file_put_contents('php://stderr', ' ** Migration cannot be rolled back, usernames have already been re-hashed' . PHP_EOL);
        exit;
    }
    /**
     * Update records in the users table to support new PHP
     * password hashing functions (since PHP 5.5)
     *
     * Hash md5 hashed passwords with brcypt
     * and convert usernames to lower-case.
     */
    public function up()
    {
        $useroverview = <<<'VIEW'
CREATE OR REPLACE VIEW useroverview AS
 SELECT u.username,
    u.password,
    u.lastcompanylogin,
    u.person_id,
    u.last_login,
    u.audit_enabled,
    u.debug_enabled,
    u.access_enabled,
    uca.usercompanyid,
    c.name AS company,
    (p.firstname::text || ' '::text) || p.surname::text AS person
   FROM users u
     LEFT JOIN company c ON u.lastcompanylogin = c.id
     LEFT JOIN user_company_access uca ON u.username::text = uca.username::text
     LEFT JOIN person p ON u.person_id = p.id;
VIEW;

        $hasrolesoverview = <<<'VIEW'
CREATE OR REPLACE VIEW hasrolesoverview AS
 SELECT hasrole.roleid,
    hasrole.username,
    hasrole.id,
    users.password,
    users.lastcompanylogin,
    users.person_id,
    roles.name AS role
   FROM hasrole
     JOIN roles ON roles.id = hasrole.roleid
     JOIN users ON users.username::text = hasrole.username::text;
VIEW;

        // drop dependent views
        $this->query('DROP VIEW useroverview');
        $this->query('DROP VIEW hasrolesoverview');

        // increase password hash length limit
        $users = $this->table('users');
        $users->changeColumn('password', 'string', array('limit' => 255))
              ->save();

        // hash current md5 hashed passwords and convert usernames to lower-case
        // in the users and po_authlimits table
        $rows = $this->fetchAll('SELECT * FROM users');
        foreach ($rows as $row){
            if (substr($row['password'], 0, 1) !== '$') {
                $md5hash = $row['password'];
                $newhash = password_hash($md5hash, PASSWORD_DEFAULT);
                $this->query("UPDATE users SET username='" . $row['username'] ."', password='" . $newhash .
                                    "' WHERE username='" . $row['username'] . "';");
            }
        }

        // restore views
        $this->query($useroverview);
        $this->query('ALTER TABLE useroverview OWNER TO "www-data"');
        $this->query($hasrolesoverview);
        $this->query('ALTER TABLE hasrolesoverview OWNER TO "www-data"');
        $this->cleanMemcache($this->cache_keys);
    }
}
