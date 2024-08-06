<?php

use Phinx\Migration\AbstractMigration;

class PiHeaderProjectTaskLink extends AbstractMigration
{
     /**
     * Migrate Up.
     */
    public function up()
    {
    	
// SQL statements to create the replacement views
    	
    	$piheaderoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.id,
    pi.invoice_number,
    pi.our_reference,
    pi.plmaster_id,
    pi.invoice_date,
    pi.transaction_type,
    pi.ext_reference,
    pi.currency_id,
    pi.rate,
    pi.gross_value,
    pi.tax_value,
    pi.tax_status_id,
    pi.net_value,
    pi.twin_currency_id,
    pi.twin_rate,
    pi.twin_gross_value,
    pi.twin_tax_value,
    pi.twin_net_value,
    pi.base_gross_value,
    pi.base_tax_value,
    pi.base_net_value,
    pi.payment_term_id,
    pi.due_date,
    pi.status,
    pi.description,
    pi.auth_date,
    pi.auth_by,
    pi.usercompanyid,
    pi.original_due_date,
    pi.created,
    pi.createdby,
    pi.alteredby,
    pi.lastupdated,
    pi.settlement_discount,
    pi.project_id,
    pi.task_id,
    (p.job_no || ' - '::text) || p.name::text AS project,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin,
    syt.description AS payment_terms,
    COALESCE(pl.line_count, 0::bigint) AS line_count
   FROM pi_header pi
   LEFT JOIN ( SELECT pi_lines.invoice_id,
            count(*) AS line_count
           FROM pi_lines
          GROUP BY pi_lines.invoice_id) pl ON pl.invoice_id = pi.id
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id
   LEFT JOIN projects p ON pi.project_id = p.id;
VIEW_WRAP;
    
    
    	$poproductinvoices = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_product_invoices AS 
 SELECT ph.id,
    ph.invoice_number,
    ph.our_reference,
    ph.plmaster_id,
    ph.invoice_date,
    ph.transaction_type,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.gross_value,
    ph.tax_value,
    ph.tax_status_id,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_gross_value,
    ph.twin_tax_value,
    ph.twin_net_value,
    ph.base_gross_value,
    ph.base_tax_value,
    ph.base_net_value,
    ph.payment_term_id,
    ph.due_date,
    ph.status,
    ph.description,
    ph.auth_date,
    ph.auth_by,
    ph.usercompanyid,
    ph.original_due_date,
    ph.created,
    ph.createdby,
    ph.alteredby,
    ph.lastupdated,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin,
    ph.payment_terms,
    ph.line_count,
    pl.id AS invoiceline_id,
    pl.productline_id,
    ppl.productline_header_id
   FROM pi_headeroverview ph
   JOIN pi_lines pl ON ph.id = pl.invoice_id
   JOIN po_product_lines ppl ON ppl.id = pl.productline_id;

VIEW_WRAP;
    
    	// Drop affected views to enable tables to be modified
    	$this->query('DROP VIEW po_product_invoices');
    	$this->query('DROP VIEW pi_headeroverview');
    	 
    
    // Modify pi_header table to add project_id and task_id
    	$piheader = $this->table('pi_header');
    	$piheader->addColumn('project_id', 'biginteger', array('null' => true,))
    	->addForeignKey('project_id', 'projects', 'id')
    	->save();
    	
    	$piheader = $this->table('pi_header');
    	$piheader->addColumn('task_id', 'biginteger', array('null' => true,))
    	->addForeignKey('task_id', 'tasks', 'id')
    	->save();
    	
    
    	// Execute SQL to re-create the views
    	$this->query($piheaderoverview);
    	$this->query('ALTER TABLE pi_headeroverview OWNER TO "www-data"');
    	$this->query($poproductinvoices);
    	$this->query('ALTER TABLE po_product_invoices OWNER TO "www-data"');
  	
    
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
		$piheaderoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW pi_headeroverview AS 
 SELECT pi.id,
    pi.invoice_number,
    pi.our_reference,
    pi.plmaster_id,
    pi.invoice_date,
    pi.transaction_type,
    pi.ext_reference,
    pi.currency_id,
    pi.rate,
    pi.gross_value,
    pi.tax_value,
    pi.tax_status_id,
    pi.net_value,
    pi.twin_currency_id,
    pi.twin_rate,
    pi.twin_gross_value,
    pi.twin_tax_value,
    pi.twin_net_value,
    pi.base_gross_value,
    pi.base_tax_value,
    pi.base_net_value,
    pi.payment_term_id,
    pi.due_date,
    pi.status,
    pi.description,
    pi.auth_date,
    pi.auth_by,
    pi.usercompanyid,
    pi.original_due_date,
    pi.created,
    pi.createdby,
    pi.alteredby,
    pi.lastupdated,
    pi.settlement_discount,
    plm.payee_name,
    c.name AS supplier,
    cum.currency,
    twc.currency AS twin,
    syt.description AS payment_terms,
    COALESCE(pl.line_count, 0::bigint) AS line_count
   FROM pi_header pi
   LEFT JOIN ( SELECT pi_lines.invoice_id,
            count(*) AS line_count
           FROM pi_lines
          GROUP BY pi_lines.invoice_id) pl ON pl.invoice_id = pi.id
   JOIN plmaster plm ON pi.plmaster_id = plm.id
   JOIN company c ON plm.company_id = c.id
   JOIN cumaster cum ON pi.currency_id = cum.id
   JOIN cumaster twc ON pi.twin_currency_id = twc.id
   JOIN syterms syt ON pi.payment_term_id = syt.id;
VIEW_WRAP;
    
    
    	$poproductinvoices = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_product_invoices AS 
 SELECT ph.id,
    ph.invoice_number,
    ph.our_reference,
    ph.plmaster_id,
    ph.invoice_date,
    ph.transaction_type,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.gross_value,
    ph.tax_value,
    ph.tax_status_id,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_gross_value,
    ph.twin_tax_value,
    ph.twin_net_value,
    ph.base_gross_value,
    ph.base_tax_value,
    ph.base_net_value,
    ph.payment_term_id,
    ph.due_date,
    ph.status,
    ph.description,
    ph.auth_date,
    ph.auth_by,
    ph.usercompanyid,
    ph.original_due_date,
    ph.created,
    ph.createdby,
    ph.alteredby,
    ph.lastupdated,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin,
    ph.payment_terms,
    ph.line_count,
    pl.id AS invoiceline_id,
    pl.productline_id,
    ppl.productline_header_id
   FROM pi_headeroverview ph
   JOIN pi_lines pl ON ph.id = pl.invoice_id
   JOIN po_product_lines ppl ON ppl.id = pl.productline_id;
VIEW_WRAP;
    	
    	$this->query('DROP VIEW po_product_invoices');
    	$this->query('DROP VIEW pi_headeroverview');
    
		// revert the pi_header table to take out project_id and task_id   
    	$piheader = $this->table('pi_header');
    	$piheader->removeColumn('project_id')
    			 ->dropForeignKey('project_id')
    	->save();
    	
		$piheader = $this->table('pi_header');
    	$piheader->removeColumn('task_id')
    			 ->dropForeignKey('task_id')
    	->save();    	
    	
    	// Execute SQL to re-create the views
    	$this->query($piheaderoverview);
    	$this->query('ALTER TABLE pi_headeroverview OWNER TO "www-data"');
    	$this->query($poproductinvoices);
    	$this->query('ALTER TABLE po_product_invoices OWNER TO "www-data"');
    }
}