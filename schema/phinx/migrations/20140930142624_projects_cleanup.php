<?php

use Phinx\Migration\AbstractMigration;

class ProjectsCleanup extends AbstractMigration
{

    //Cache keys to be cleaned on migration/rollback
    var $cache_keys = array(
    'uzerp[searches][admin][project_equipment_overview_]',
    'uzerp[searches][admin][projectsoverview_]',
    'uzerp[table_fields][project_categories]',
    'uzerp[table_fields][project_equipment]',
    'uzerp[table_fields][project_equipment_overview]',
    'uzerp[table_fields][project_hours_overview]',
    'uzerp[table_fields][project_resources]',
    'uzerp[table_fields][projects]',
    'uzerp[table_fields][projectsoverview]',
    'uzerp[table_fields][resource_templates_overview]',
    'uzerp[table_fields][project_hours_overview]',
    'uzerp[table_fields][opportunitiesoverview]',
    'uzerp[table_fields][resource_templates]',
    'uzerp[table_fields][hours]'
    );
    
    /**
    * Remove listed keys from mencache
    *
    * @param: array $keys array of key names to remove
    */    
    private function CleanMemcache($keys)
    {
        $memcache = new Memcached();
        $memcache->addServer("localhost", 11211); 
        file_put_contents('php://stderr', 'Removing keys from cache...' . PHP_EOL);
        foreach ($keys as $key)
        {
            $memcache->delete($key);
            file_put_contents('php://stderr', 'removed '. $key . PHP_EOL);
        }       
    }

    
    /**
     * Migrate Up.
     */
    public function up()
    {
 	
        // SQL statements to create the replacement views
        $resource_templates_overview = <<<'VIEW'
CREATE OR REPLACE VIEW resource_templates_overview AS 
 SELECT rt.id,
    rt.name,
    rt.person_id,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    rt.mfresource_id,
    (rm.resource_code::text || ' - '::text) || rm.description::text AS resource,
    rm.resource_rate,
    rt.resource_type_id,
    ry.name AS resource_type,
    rt.standard_rate,
    rt.overtime_rate,
    rt.usercompanyid
   FROM resource_templates rt
   LEFT JOIN person p ON rt.person_id = p.id
   LEFT JOIN mf_resources rm ON rt.mfresource_id = rm.id
   LEFT JOIN resource_types ry ON rt.resource_type_id = ry.id;
VIEW;

        $hoursoverview = <<<'VIEW'
CREATE OR REPLACE VIEW hoursoverview AS 
 SELECT h.id,
    h.start_time,
    h.end_time,
    h.duration,
    h.description,
    h.project_id,
    h.task_id,
    h.ticket_id,
    h.opportunity_id,
    h.billable,
    h.invoiced,
    h.created,
    h.lastupdated,
    h.usercompanyid,
    h.type_id,
    h.person_id,
    h.createdby,
    h.updatedby,
    (u.firstname::text || ' '::text) || u.surname::text AS person,
    ht.name AS type,
    p.name AS project,
    t.name AS task,
    k.*::name AS ticket,
    o.name AS opportunity
   FROM hours h
   JOIN person u ON u.id = h.person_id
   JOIN hour_types ht ON ht.id = h.type_id
   LEFT JOIN projects p ON p.id = h.project_id
   LEFT JOIN tasks t ON t.id = h.task_id
   LEFT JOIN tickets k ON k.id = h.ticket_id
   LEFT JOIN opportunities o ON o.id = h.opportunity_id;
VIEW;

        $task_hours_overview = <<<'VIEW'
CREATE OR REPLACE VIEW task_hours_overview AS 
 SELECT (h.start_time || ' '::text) || h.person_id AS id,
    t.id AS task_id,
    t.name,
    t.usercompanyid,
    h.id AS hour_id,
    h.start_time,
    h.duration,
    m.resource_rate,
    u.id AS person_id,
    (u.firstname::text || ' '::text) || u.surname::text AS person
   FROM tasks t
     JOIN hours h ON h.task_id = t.id
     JOIN person u ON u.id = h.person_id
     JOIN project_resources r ON r.person_id = h.person_id AND r.task_id = t.id
     JOIN mf_resources m ON m.id = r.resource_id
VIEW;

        $project_hours_overview = <<<'VIEW'
CREATE OR REPLACE VIEW project_hours_overview AS 
 SELECT (h.start_time || ' '::text) || h.person_id AS id,
    p.id AS project_id,
    p.name,
    p.usercompanyid,
    h.id AS hour_id,
    h.start_time,
    h.duration,
    (m.resource_code::text || ' - '::text) || m.description::text AS resource,
    m.resource_rate,
    u.id AS person_id,
    (u.firstname::text || ' '::text) || u.surname::text AS person
   FROM projects p
     JOIN hours h ON h.project_id = p.id
     JOIN person u ON u.id = h.person_id
     JOIN project_resources r ON r.person_id = h.person_id AND r.project_id = p.id
     JOIN mf_resources m ON m.id = r.resource_id
VIEW;

        $opportunitiesoverview = <<<'VIEW'
CREATE OR REPLACE VIEW opportunitiesoverview AS 
 SELECT o.id,
    o.status_id,
    o.campaign_id,
    o.company_id,
    o.person_id,
    o.owner,
    o.name,
    o.description,
    o.value,
    o.cost,
    o.probability,
    o.enddate,
    o.usercompanyid,
    o.type_id,
    o.source_id,
    o.nextstep,
    o.assigned,
    o.created,
    o.lastupdated,
    o.alteredby,
    c.name AS company,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    cam.name AS campaign,
    os.name AS source,
    ot.name AS type,
    opportunitystatus.name AS status,
        CASE
            WHEN opportunitystatus.* IS NULL THEN false
            ELSE opportunitystatus.open
        END AS open,
        CASE
            WHEN opportunitystatus.* IS NULL THEN false
            ELSE opportunitystatus.won
        END AS won
   FROM opportunities o
   LEFT JOIN company c ON o.company_id = c.id
   LEFT JOIN person p ON o.person_id = p.id
   LEFT JOIN campaigns cam ON o.campaign_id = cam.id
   LEFT JOIN opportunitysource os ON o.source_id = os.id
   LEFT JOIN opportunitytype ot ON o.type_id = ot.id
   LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id;
VIEW;

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
   LEFT JOIN users u ON pr.person_id = u.person_id
VIEW;
        $poheaderoverview = <<<'VIEW'
CREATE OR REPLACE VIEW po_headeroverview AS
 SELECT po.id,
    po.order_number,
    po.plmaster_id,
    po.del_address_id,
    po.order_date,
    po.due_date,
    po.ext_reference,
    po.currency_id,
    po.rate,
    po.net_value,
    po.twin_currency_id,
    po.twin_rate,
    po.twin_net_value,
    po.base_net_value,
    po.type,
    po.status,
    po.description,
    po.usercompanyid,
    po.date_authorised,
    po.raised_by,
    po.authorised_by,
    po.created,
    po.owner,
    po.lastupdated,
    po.alteredby,
    po.project_id,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin_currency,
    pr.username AS raised_by_person,
    pa.username AS authorised_by_person,
    p.job_no ||' - '|| p.name AS project,
    da.address AS del_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id
   LEFT JOIN addressoverview da ON po.del_address_id = da.id
VIEW;


        $poproductorders = <<<'VIEW'
CREATE OR REPLACE VIEW po_product_orders AS 
 SELECT ph.id,
    ph.order_number,
    ph.plmaster_id,
    ph.del_address_id,
    ph.order_date,
    ph.due_date,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_net_value,
    ph.base_net_value,
    ph.type,
    ph.status,
    ph.description,
    ph.usercompanyid,
    ph.date_authorised,
    ph.raised_by,
    ph.authorised_by,
    ph.created,
    ph.owner,
    ph.lastupdated,
    ph.alteredby,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin_currency,
    ph.raised_by_person,
    ph.authorised_by_person,
    ph.project,
    pl.id AS orderline_id,
    pl.productline_id,
    pl.status AS line_status,
    ppl.productline_header_id
   FROM po_headeroverview ph
   JOIN po_lines pl ON ph.id = pl.order_id
   JOIN po_product_lines ppl ON ppl.id = pl.productline_id
VIEW;

        // Drop affected views to enable tables to be modified
        $this->query('DROP VIEW resource_templates_overview');
        $this->query('DROP VIEW hoursoverview');
        $this->query('DROP VIEW task_hours_overview');
        $this->query('DROP VIEW project_hours_overview');
        $this->query('DROP VIEW opportunitiesoverview');
        $this->query('DROP VIEW projectsoverview');
        $this->query('DROP VIEW po_product_orders');
        $this->query('DROP VIEW po_headeroverview');
               
        
        // Modify resource_templates table
        $resource_templates = $this->table('resource_templates');
        $resource_templates->removeColumn('quantity')
                           ->removeColumn('cost')
                           ->addColumn('mfresource_id', 'integer', array('null' => true,))
                           ->addColumn('created', 'timestamp', array('default' => 'now()'))
                           ->addColumn('createdby', 'string', array('null' => true,))
                           ->addColumn('alteredby', 'string', array('null' => true,))
                           ->addColumn('lastupdated', 'timestamp', array('default' => 'now()'))
                           ->addForeignKey('mfresource_id', 'mf_resources', 'id', array('delete'=> 'NO_ACTION', 'update'=> 'NO_ACTION'))
                           ->save();
        
        // Modify hours table
        $hours = $this->table('hours');
        $hours->removeColumn('overtime')
              ->changeColumn('person_id', 'integer', array('null' => false,))
              ->save();
              
        // Modify projects and opportunities tables
        // Note: precsion and scale are not supported by the phinx postgresql adaptor at v0.3.7
        $this->query("ALTER TABLE projects ADD COLUMN value numeric(10,2) DEFAULT 0.0");
        $this->query("ALTER TABLE projects ALTER COLUMN cost TYPE numeric(10,2)");
        $this->query("ALTER TABLE projects ALTER COLUMN cost SET DEFAULT 0.0");
        $this->query("ALTER TABLE opportunities ADD COLUMN value numeric(10,2) DEFAULT 0.0");
        
        // Execute SQL to re-create the views
        $this->query($resource_templates_overview);
        $this->query('ALTER TABLE resource_templates_overview OWNER TO "www-data"');
        $this->query($task_hours_overview);
        $this->query('ALTER TABLE task_hours_overview OWNER TO "www-data"');
        $this->query($project_hours_overview);
        $this->query('ALTER TABLE project_hours_overview OWNER TO "www-data"');
        $this->query($hoursoverview);
        $this->query('ALTER TABLE hoursoverview OWNER TO "www-data"');
        $this->query($opportunitiesoverview);
        $this->query('ALTER TABLE opportunitiesoverview OWNER TO "www-data"');
        $this->query($projectsoverview);
        $this->query('ALTER TABLE projectsoverview OWNER TO "www-data"');
        $this->query($poheaderoverview);
        $this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
        $this->query($poproductorders);
        $this->query('ALTER TABLE po_product_orders OWNER TO "www-data"');

        // Clear memcache keys
        $this->CleanMemcache($this->cache_keys);
    }


    /**
    * Migrate Down.
    */
    public function down()
    {
        $resource_templates_overview = <<<'VIEW'
CREATE OR REPLACE VIEW resource_templates_overview AS 
 SELECT rt.id,
    rt.name,
    rt.person_id,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    rt.resource_type_id,
    ry.name AS resource_type,
    rt.standard_rate,
    rt.overtime_rate,
    rt.quantity,
    rt.cost,
    rt.usercompanyid
   FROM resource_templates rt
     LEFT JOIN person p ON rt.person_id = p.id
     LEFT JOIN resource_types ry ON rt.resource_type_id = ry.id
VIEW;

        $hoursoverview = <<<'VIEW'
CREATE OR REPLACE VIEW hoursoverview AS 
 SELECT h.id,
    h.start_time,
    h.end_time,
    h.duration,
    h.description,
    h.project_id,
    h.task_id,
    h.ticket_id,
    h.opportunity_id,
    h.billable,
    h.invoiced,
    h.overtime,
    h.created,
    h.lastupdated,
    h.usercompanyid,
    h.type_id,
    h.person_id,
    h.createdby,
    h.updatedby,
    (u.firstname::text || ' '::text) || u.surname::text AS person,
    ht.name AS type,
    p.name AS project,
    t.name AS task,
    k.*::name AS ticket,
    o.name AS opportunity
   FROM hours h
     JOIN person u ON u.id = h.person_id
     JOIN hour_types ht ON ht.id = h.type_id
     LEFT JOIN projects p ON p.id = h.project_id
     LEFT JOIN tasks t ON t.id = h.task_id
     LEFT JOIN tickets k ON k.id = h.ticket_id
     LEFT JOIN opportunities o ON o.id = h.opportunity_id
VIEW;

        $task_hours_overview = <<<'VIEW'
CREATE OR REPLACE VIEW task_hours_overview AS 
 SELECT (h.start_time || ' '::text) || h.person_id AS id,
    t.id AS task_id,
    t.name,
    t.usercompanyid,
    h.id AS hour_id,
    h.start_time,
    h.duration,
    m.resource_rate,
    u.id AS person_id,
    (u.firstname::text || ' '::text) || u.surname::text AS person
   FROM tasks t
     JOIN hours h ON h.task_id = t.id
     JOIN person u ON u.id = h.person_id
     JOIN project_resources r ON r.person_id = h.person_id AND r.task_id = t.id
     JOIN mf_resources m ON m.id = r.resource_id
VIEW;

        $project_hours_overview = <<<'VIEW'
CREATE OR REPLACE VIEW project_hours_overview AS 
 SELECT (h.start_time || ' '::text) || h.person_id AS id,
    p.id AS project_id,
    p.name,
    p.usercompanyid,
    h.id AS hour_id,
    h.start_time,
    h.duration,
    m.resource_rate,
    u.id AS person_id,
    (u.firstname::text || ' '::text) || u.surname::text AS person
   FROM projects p
     JOIN hours h ON h.project_id = p.id
     JOIN person u ON u.id = h.person_id
     JOIN project_resources r ON r.person_id = h.person_id AND r.project_id = p.id
     JOIN mf_resources m ON m.id = r.resource_id
VIEW;

        $opportunitiesoverview = <<<'VIEW'
CREATE OR REPLACE VIEW opportunitiesoverview AS 
 SELECT o.id,
    o.status_id,
    o.campaign_id,
    o.company_id,
    o.person_id,
    o.owner,
    o.name,
    o.description,
    o.cost,
    o.probability,
    o.enddate,
    o.usercompanyid,
    o.type_id,
    o.source_id,
    o.nextstep,
    o.assigned,
    o.created,
    o.lastupdated,
    o.alteredby,
    c.name AS company,
    (p.firstname::text || ' '::text) || p.surname::text AS person,
    cam.name AS campaign,
    os.name AS source,
    ot.name AS type,
    opportunitystatus.name AS status,
        CASE
            WHEN opportunitystatus.* IS NULL THEN false
            ELSE opportunitystatus.open
        END AS open,
        CASE
            WHEN opportunitystatus.* IS NULL THEN false
            ELSE opportunitystatus.won
        END AS won
   FROM opportunities o
     LEFT JOIN company c ON o.company_id = c.id
     LEFT JOIN person p ON o.person_id = p.id
     LEFT JOIN campaigns cam ON o.campaign_id = cam.id
     LEFT JOIN opportunitysource os ON o.source_id = os.id
     LEFT JOIN opportunitytype ot ON o.type_id = ot.id
     LEFT JOIN opportunitystatus ON o.status_id = opportunitystatus.id
VIEW;

$projectsoverview = <<<'VIEW'
CREATE OR REPLACE VIEW projectsoverview AS 
 SELECT pr.id,
    pr.name,
    pr.start_date,
    pr.end_date,
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
VIEW;

        $poheaderoverview = <<<'VIEW'
CREATE OR REPLACE VIEW po_headeroverview AS 
 SELECT po.id,
    po.order_number,
    po.plmaster_id,
    po.del_address_id,
    po.order_date,
    po.due_date,
    po.ext_reference,
    po.currency_id,
    po.rate,
    po.net_value,
    po.twin_currency_id,
    po.twin_rate,
    po.twin_net_value,
    po.base_net_value,
    po.type,
    po.status,
    po.description,
    po.usercompanyid,
    po.date_authorised,
    po.raised_by,
    po.authorised_by,
    po.created,
    po.owner,
    po.lastupdated,
    po.alteredby,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin_currency,
    pr.username AS raised_by_person,
    pa.username AS authorised_by_person,
    p.name AS project,
    da.address AS del_address,
    da.street1,
    da.street2,
    da.street3,
    da.town,
    da.county,
    da.postcode,
    da.country,
    da.countrycode
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id
   LEFT JOIN addressoverview da ON po.del_address_id = da.id
VIEW;
        
        
        $poproductorders = <<<'VIEW'
CREATE OR REPLACE VIEW po_product_orders AS 
 SELECT ph.id,
    ph.order_number,
    ph.plmaster_id,
    ph.del_address_id,
    ph.order_date,
    ph.due_date,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_net_value,
    ph.base_net_value,
    ph.type,
    ph.status,
    ph.description,
    ph.usercompanyid,
    ph.date_authorised,
    ph.raised_by,
    ph.authorised_by,
    ph.created,
    ph.owner,
    ph.lastupdated,
    ph.alteredby,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin_currency,
    ph.raised_by_person,
    ph.authorised_by_person,
    ph.project,
    pl.id AS orderline_id,
    pl.productline_id,
    pl.status AS line_status,
    ppl.productline_header_id
   FROM po_headeroverview ph
   JOIN po_lines pl ON ph.id = pl.order_id
   JOIN po_product_lines ppl ON ppl.id = pl.productline_id
VIEW;
        
        
        $this->query('DROP VIEW resource_templates_overview');
        $this->query('DROP VIEW hoursoverview');
        $this->query('DROP VIEW task_hours_overview');
        $this->query('DROP VIEW project_hours_overview');
        $this->query('DROP VIEW opportunitiesoverview');
        $this->query('DROP VIEW projectsoverview');
        $this->query('DROP VIEW po_product_orders');
        $this->query('DROP VIEW po_headeroverview');
        
        $resource_templates = $this->table('resource_templates');
        $resource_templates->addColumn('quantity', 'integer', array('null' => true,))
                           ->addColumn('cost', 'float', array('null' => true,))
                           ->removeColumn('mfresource_id')
                           ->removeColumn('created')
                           ->removeColumn('createdby')
                           ->removeColumn('alteredby')
                           ->removeColumn('lastupdated')
                           ->dropForeignKey('mfresource_id')
                           ->save();
        
        $hours = $this->table('hours');
        $hours->addColumn('overtime', 'boolean')
              ->changeColumn('person_id', 'integer', array('null' => true,))
              ->save();
              
        //modify projects table
        $projects = $this->table('projects');
        $projects->removeColumn('value')
                 ->changeColumn('cost', 'biginteger', array('null' => true,))
                 ->save();
        
        //modify opportunities table
        $opportunities = $this->table('opportunities');
        $opportunities->removeColumn('value')
                      ->save();
        
        // Execute SQL to re-create the views
        $this->query($resource_templates_overview);
        $this->query('ALTER TABLE resource_templates_overview OWNER TO "www-data"');
        $this->query($task_hours_overview);
        $this->query('ALTER TABLE task_hours_overview OWNER TO "www-data"');
        $this->query($project_hours_overview);
        $this->query('ALTER TABLE project_hours_overview OWNER TO "www-data"');
        $this->query($hoursoverview);
        $this->query('ALTER TABLE hoursoverview OWNER TO "www-data"');
        $this->query($opportunitiesoverview);
        $this->query('ALTER TABLE opportunitiesoverview OWNER TO "www-data"');
        $this->query($projectsoverview);
        $this->query('ALTER TABLE projectsoverview OWNER TO "www-data"');
        $this->query($poheaderoverview);
        $this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
        $this->query($poproductorders);
        $this->query('ALTER TABLE po_product_orders OWNER TO "www-data"');

        // Clear memcache keys
        $this->CleanMemcache($this->cache_keys);
    }
}
