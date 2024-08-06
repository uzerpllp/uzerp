<?php
/**
 * Phinx migration - po_product_invoices_add_project_id
 * 
 * Adds project and project_id column to the view public.po_product_invoices
 * 
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later; See LICENSE
 * 
 * @since 1.4.1
 */

use Phinx\Migration\AbstractMigration;

class PoProductInvoicesAddProjectId extends AbstractMigration
{  
    /**
     * Migrate Up.
     */
    public function up()
    {
        $po_product_invoices = <<<'VIEW_WRAP'
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
    ph.project,
    ph.project_id,
    pl.id AS invoiceline_id,
    pl.productline_id,
    ppl.productline_header_id
   FROM pi_headeroverview ph
     JOIN pi_lines pl ON ph.id = pl.invoice_id
     JOIN po_product_lines ppl ON ppl.id = pl.productline_id;
VIEW_WRAP;

        // Drop and recreate the view
        $this->query('DROP VIEW po_product_invoices');
        $this->query($po_product_invoices);
        $this->query('ALTER TABLE po_product_invoices OWNER TO "www-data";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $po_product_invoices = <<<'VIEW_WRAP'
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

        // Drop and recreate the view
        $this->query('DROP VIEW po_product_invoices');
        $this->query($po_product_invoices);
        $this->query('ALTER TABLE po_product_invoices OWNER TO "www-data";');
    }
}
