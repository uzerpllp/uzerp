<?php

use Phinx\Migration\AbstractMigration;

class PoHeaderProjectTaskLink extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
    
    	// SQL statements to create the replacement views
    	
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
    po.task_id,
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
    	$this->query('DROP VIEW po_product_orders');
    	$this->query('DROP VIEW po_headeroverview');
    	 
    
    	// Modify po_header table to add task_id
    	$poheader = $this->table('po_header');
    	$poheader->addColumn('task_id', 'biginteger', array('null' => true,))
    	->addForeignKey('task_id', 'tasks', 'id')
    	->save();
    	
    
    	// Execute SQL to re-create the views
    	$this->query($poheaderoverview);
    	$this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
    	$this->query($poproductorders);
    	$this->query('ALTER TABLE po_product_orders OWNER TO "www-data"');
  
    }
    
    
    /**
     * Migrate Down.
     */
    public function down()
    {
    
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
    	
    	$this->query('DROP VIEW po_product_orders');
    	$this->query('DROP VIEW po_headeroverview');
    
		// revert the po_header table to take out task_id   
    	$poheader = $this->table('po_header');
    	$poheader->removeColumn('task_id')
    			 ->dropForeignKey('task_id')
    	->save();
    	
    	// Execute SQL to re-create the views
    	$this->query($poheaderoverview);
    	$this->query('ALTER TABLE po_headeroverview OWNER TO "www-data"');
    	$this->query($poproductorders);
    	$this->query('ALTER TABLE po_product_orders OWNER TO "www-data"');
    }
    
}