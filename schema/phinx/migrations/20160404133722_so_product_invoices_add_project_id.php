<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration
 *
 * Add project_id from so_header to so_product_invoices view
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class SoProductInvoicesAddProjectId extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_product_invoices]'
    );

    /*
     * Add project_id to view
     */
    public function up()
    {
        $so_product_invoices = <<<'VIEW'
CREATE OR REPLACE VIEW so_product_invoices AS
 SELECT sh.id,
    sh.invoice_number,
    sh.sales_order_id,
    sh.slmaster_id,
    sh.invoice_date,
    sh.transaction_type,
    sh.ext_reference,
    sh.currency_id,
    sh.rate,
    sh.gross_value,
    sh.tax_value,
    sh.net_value,
    sh.twin_currency_id,
    sh.twin_rate,
    sh.twin_gross_value,
    sh.twin_tax_value,
    sh.twin_net_value,
    sh.base_gross_value,
    sh.base_tax_value,
    sh.base_net_value,
    sh.payment_term_id,
    sh.due_date,
    sh.status,
    sh.description,
    sh.usercompanyid,
    sh.tax_status_id,
    sh.settlement_discount,
    sh.delivery_note,
    sh.despatch_date,
    sh.date_printed,
    sh.print_count,
    sh.del_address_id,
    sh.inv_address_id,
    sh.original_due_date,
    sh.created,
    sh.createdby,
    sh.alteredby,
    sh.lastupdated,
    sh.person_id,
    sh.sales_order_number,
    sh.customer,
    sh.currency,
    sh.twin,
    sh.payment_terms,
    sh.tax_status,
    sh.sl_analysis_id,
    sh.invoice_method,
    sh.edi_invoice_definition_id,
    sh.email_invoice,
    sh.name,
    sh.person,
    sh.project,
    sh.project_id,
    sh.line_count,
    sl.id AS invoiceline_id,
    sl.productline_id,
    spl.productline_header_id
   FROM si_headeroverview sh
     JOIN si_lines sl ON sh.id = sl.invoice_id
     JOIN so_product_lines spl ON spl.id = sl.productline_id;
VIEW;

        $this->query('DROP VIEW so_product_invoices');
        $this->query($so_product_invoices);
        $this->query('ALTER TABLE so_product_invoices OWNER TO "www-data"');
        $this->cleanMemcache($this->cache_keys);
    }

    /*
     * Remove project_id from view
     */
    public function down()
    {
        $so_product_invoices = <<<'VIEW'
CREATE OR REPLACE VIEW so_product_invoices AS
 SELECT sh.id,
    sh.invoice_number,
    sh.sales_order_id,
    sh.slmaster_id,
    sh.invoice_date,
    sh.transaction_type,
    sh.ext_reference,
    sh.currency_id,
    sh.rate,
    sh.gross_value,
    sh.tax_value,
    sh.net_value,
    sh.twin_currency_id,
    sh.twin_rate,
    sh.twin_gross_value,
    sh.twin_tax_value,
    sh.twin_net_value,
    sh.base_gross_value,
    sh.base_tax_value,
    sh.base_net_value,
    sh.payment_term_id,
    sh.due_date,
    sh.status,
    sh.description,
    sh.usercompanyid,
    sh.tax_status_id,
    sh.settlement_discount,
    sh.delivery_note,
    sh.despatch_date,
    sh.date_printed,
    sh.print_count,
    sh.del_address_id,
    sh.inv_address_id,
    sh.original_due_date,
    sh.created,
    sh.createdby,
    sh.alteredby,
    sh.lastupdated,
    sh.person_id,
    sh.sales_order_number,
    sh.customer,
    sh.currency,
    sh.twin,
    sh.payment_terms,
    sh.tax_status,
    sh.sl_analysis_id,
    sh.invoice_method,
    sh.edi_invoice_definition_id,
    sh.email_invoice,
    sh.name,
    sh.person,
    sh.line_count,
    sl.id AS invoiceline_id,
    sl.productline_id,
    spl.productline_header_id
   FROM si_headeroverview sh
     JOIN si_lines sl ON sh.id = sl.invoice_id
     JOIN so_product_lines spl ON spl.id = sl.productline_id;
VIEW;

        $this->query('DROP VIEW so_product_invoices');
        $this->query($so_product_invoices);
        $this->query('ALTER TABLE so_product_invoices OWNER TO "www-data"');
        $this->cleanMemcache($this->cache_keys);
    }
}
