<?php


use UzerpPhinx\UzerpMigration;

class PurchaseOrderLineOverviewAddCodes extends UzerpMigration
{
    /**
     * Add commodity code
     */
    public function up()
    {
        $view = <<<VIEW_WRAP
CREATE OR REPLACE VIEW po_linesoverview AS 
SELECT pl.id,
    pl.order_id,
    pl.line_number,
    pl.productline_id,
    pl.stuom_id,
    pl.item_description,
    pl.order_qty,
    pl.price,
    pl.currency_id,
    pl.rate,
    pl.net_value,
    pl.twin_currency_id,
    pl.twin_rate,
    pl.twin_net_value,
    pl.base_net_value,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.line_discount,
    pl.os_qty,
    pl.revised_qty,
    pl.del_qty,
    pl.due_delivery_date,
    pl.actual_delivery_date,
    pl.gr_note,
    pl.status,
    pl.usercompanyid,
    pl.stitem_id,
    pl.tax_rate_id,
    pph.commodity_code,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.description,
    pl.glaccount_centre_id,
    ph.due_date,
    ph.order_date,
    ph.order_number,
    ph.plmaster_id,
    ph.receive_action,
    ph.type,
    ph.net_value AS order_value,
    cu.currency,
    (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre,
    (gla.account::text || ' - '::text) || gla.description::text AS glaccount,
    tax.description AS taxrate,
    ph.status AS order_status,
    plm.payee_name,
    c.name AS supplier,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    u.uom_name
    FROM po_lines pl
    JOIN gl_centres glc ON glc.id = pl.glcentre_id
    JOIN gl_accounts gla ON gla.id = pl.glaccount_id
    JOIN taxrates tax ON tax.id = pl.tax_rate_id
    JOIN cumaster cu ON cu.id = pl.currency_id
    JOIN po_header ph ON ph.id = pl.order_id
    JOIN plmaster plm ON ph.plmaster_id = plm.id
    JOIN company c ON plm.company_id = c.id
    LEFT JOIN st_items i ON i.id = pl.stitem_id
    LEFT JOIN st_uoms u ON u.id = pl.stuom_id
    LEFT JOIN po_product_lines ppl ON ppl.id = pl.productline_id
    LEFT JOIN po_product_lines_header pph ON pph.id = ppl.productline_header_id;
VIEW_WRAP;
        $viewname = 'po_linesoverview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }

    /**
     * Remove commodity code
     */
    public function down()
    {
        $view = <<<VIEW_WRAP
CREATE OR REPLACE VIEW po_linesoverview AS 
SELECT pl.id,
    pl.order_id,
    pl.line_number,
    pl.productline_id,
    pl.stuom_id,
    pl.item_description,
    pl.order_qty,
    pl.price,
    pl.currency_id,
    pl.rate,
    pl.net_value,
    pl.twin_currency_id,
    pl.twin_rate,
    pl.twin_net_value,
    pl.base_net_value,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.line_discount,
    pl.os_qty,
    pl.revised_qty,
    pl.del_qty,
    pl.due_delivery_date,
    pl.actual_delivery_date,
    pl.gr_note,
    pl.status,
    pl.usercompanyid,
    pl.stitem_id,
    pl.tax_rate_id,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.description,
    pl.glaccount_centre_id,
    ph.due_date,
    ph.order_date,
    ph.order_number,
    ph.plmaster_id,
    ph.receive_action,
    ph.type,
    ph.net_value AS order_value,
    cu.currency,
    (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre,
    (gla.account::text || ' - '::text) || gla.description::text AS glaccount,
    tax.description AS taxrate,
    ph.status AS order_status,
    plm.payee_name,
    c.name AS supplier,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    u.uom_name
    FROM po_lines pl
    JOIN gl_centres glc ON glc.id = pl.glcentre_id
    JOIN gl_accounts gla ON gla.id = pl.glaccount_id
    JOIN taxrates tax ON tax.id = pl.tax_rate_id
    JOIN cumaster cu ON cu.id = pl.currency_id
    JOIN po_header ph ON ph.id = pl.order_id
    JOIN plmaster plm ON ph.plmaster_id = plm.id
    JOIN company c ON plm.company_id = c.id
    LEFT JOIN st_items i ON i.id = pl.stitem_id
    LEFT JOIN st_uoms u ON u.id = pl.stuom_id;
VIEW_WRAP;
        $viewname = 'po_linesoverview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }
}
