<?php


use UzerpPhinx\UzerpMigration;

class PurchaseInvoiceLineOverviewAddCodes extends UzerpMigration
{
    /**
     * Add commodity code
     */
    public function up()
    {
        $view = <<<VIEW
CREATE OR REPLACE VIEW pi_linesoverview AS
SELECT pl.id,
pl.invoice_id,
pl.line_number,
pl.purchase_order_id,
pl.order_line_id,
pl.stitem_id,
pl.item_description,
pl.purchase_qty,
pl.purchase_price,
pl.currency_id,
pl.rate,
pl.gross_value,
pl.tax_value,
pl.tax_rate_id,
pl.net_value,
pl.twin_currency_id,
pl.twin_rate,
pl.twin_gross_value,
pl.twin_tax_value,
pl.twin_net_value,
pl.base_gross_value,
pl.base_tax_value,
pl.base_net_value,
pl.glaccount_id,
pl.glcentre_id,
pph.commodity_code,
pl.job,
pl.description,
pl.delivery_note,
pl.usercompanyid,
pl.created,
pl.createdby,
pl.alteredby,
pl.lastupdated,
pl.glaccount_centre_id,
pl.productline_id,
pl.invoice_line_id,
pl.grn_id,
pl.gr_number,
ph.invoice_date,
ph.invoice_number,
ph.transaction_type,
ph.plmaster_id,
poh.order_number,
c.name AS supplier,
i.item_code,
(i.item_code::text || ' - '::text) || i.description::text AS stitem
FROM pi_lines pl
JOIN pi_header ph ON ph.id = pl.invoice_id
JOIN plmaster plm ON ph.plmaster_id = plm.id
JOIN company c ON plm.company_id = c.id
LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
LEFT JOIN st_items i ON i.id = pl.stitem_id
        LEFT JOIN po_product_lines ppl ON ppl.id = pl.productline_id
        LEFT JOIN po_product_lines_header pph ON pph.id = ppl.productline_header_id;
VIEW;

        $viewname = 'pi_linesoverview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }

    public function down()
    {
        $view = <<<VIEW
CREATE OR REPLACE VIEW pi_linesoverview AS 
SELECT pl.id,
pl.invoice_id,
pl.line_number,
pl.purchase_order_id,
pl.order_line_id,
pl.stitem_id,
pl.item_description,
pl.purchase_qty,
pl.purchase_price,
pl.currency_id,
pl.rate,
pl.gross_value,
pl.tax_value,
pl.tax_rate_id,
pl.net_value,
pl.twin_currency_id,
pl.twin_rate,
pl.twin_gross_value,
pl.twin_tax_value,
pl.twin_net_value,
pl.base_gross_value,
pl.base_tax_value,
pl.base_net_value,
pl.glaccount_id,
pl.glcentre_id,
pl.job,
pl.description,
pl.delivery_note,
pl.usercompanyid,
pl.created,
pl.createdby,
pl.alteredby,
pl.lastupdated,
pl.glaccount_centre_id,
pl.productline_id,
pl.invoice_line_id,
pl.grn_id,
pl.gr_number,
ph.invoice_date,
ph.invoice_number,
ph.transaction_type,
ph.plmaster_id,
poh.order_number,
c.name AS supplier,
i.item_code,
(i.item_code::text || ' - '::text) || i.description::text AS stitem
FROM pi_lines pl
JOIN pi_header ph ON ph.id = pl.invoice_id
JOIN plmaster plm ON ph.plmaster_id = plm.id
JOIN company c ON plm.company_id = c.id
LEFT JOIN po_header poh ON pl.purchase_order_id = poh.id
LEFT JOIN st_items i ON i.id = pl.stitem_id;
VIEW;

        $viewname = 'pi_linesoverview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }
}
