<?php


use UzerpPhinx\UzerpMigration;
use Ramsey\Uuid\Uuid;

class SetUserUuid extends UzerpMigration
{
    /**
     * Add uuid to each user
     *
     * @return void
     */
    public function up()
    {
        $users = $this->fetchAll('SELECT username, uuid FROM users');
        foreach($users as $user) {
            if (empty($user['uuid'])) {
                $uuid = Uuid::uuid4();
                $this->query("UPDATE users SET uuid = '{$uuid}' WHERE username = '{$user['username']}'");
            }
        }
    }

    public function down() {
        return;
    }
}
