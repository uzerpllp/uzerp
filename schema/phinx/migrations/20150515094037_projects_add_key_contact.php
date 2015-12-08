<?php

use Phinx\Migration\AbstractMigration;

class ProjectsAddKeyContact extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
		$projectsoverview = <<<'VIEW'
CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.id,
    pr.name,
    (kc.firstname::text || ' '::text) || kc.surname::text AS key_contact,
    pr.start_date,
    pr.end_date,
    pr.value,
    pr.cost,
    pr.url,
    pr.phase_id,
    pr.archived,
    pr.description,
    pr.company_id,
    pr.usercompanyid,
    pr.owner,
    pr.template,
    pr.job_no,
    pr.invoiced,
    pr.opportunity_id,
    pr.category_id,
    pr.person_id,
    pr.alteredby,
    pr.created,
    pr.lastupdated,
    pr.work_type_id,
    pr.key_contact_id,
    pr.consultant_details,
    pr.createdby,
    pr.status,
    c.name AS company,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    cat.name AS category,
    wt.title AS work_type,
    ph.name AS phase,
    u.username AS usernameaccess
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN users u ON pr.person_id = u.person_id
   LEFT JOIN person kc ON pr.key_contact_id = kc.id;
VIEW;

		$this->query("select deps_save_and_drop_dependencies('public', 'projectsoverview')");
		$this->query('DROP VIEW projectsoverview');
		$this->query($projectsoverview);
		$this->query('ALTER TABLE projectsoverview OWNER TO "www-data"');
		$this->query("select deps_restore_dependencies('public', 'projectsoverview')");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
		$projectsoverview = <<<'VIEW'
CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.id,
    pr.name,
    pr.start_date,
    pr.end_date,
    pr.value,
    pr.cost,
    pr.url,
    pr.phase_id,
    pr.archived,
    pr.description,
    pr.company_id,
    pr.usercompanyid,
    pr.owner,
    pr.template,
    pr.job_no,
    pr.invoiced,
    pr.opportunity_id,
    pr.category_id,
    pr.person_id,
    pr.alteredby,
    pr.created,
    pr.lastupdated,
    pr.work_type_id,
    pr.key_contact_id,
    pr.consultant_details,
    pr.createdby,
    pr.status,
    c.name AS company,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    cat.name AS category,
    wt.title AS work_type,
    ph.name AS phase,
    u.username AS usernameaccess
   FROM projects pr
   LEFT JOIN company c ON pr.company_id = c.id
   LEFT JOIN person p ON pr.person_id = p.id
   LEFT JOIN project_categories cat ON pr.category_id = c.id
   LEFT JOIN project_work_types wt ON pr.work_type_id = wt.id
   LEFT JOIN project_phases ph ON pr.phase_id = ph.id
   LEFT JOIN users u ON pr.person_id = u.person_id;
VIEW;

		$this->query("select deps_save_and_drop_dependencies('public', 'projectsoverview')");
		$this->query('DROP VIEW projectsoverview');
		$this->query($projectsoverview);
		$this->query('ALTER TABLE projectsoverview OWNER TO "www-data"');
		$this->query("select deps_restore_dependencies('public', 'projectsoverview')");
    }
}