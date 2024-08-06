<?php

use UzerpPhinx\UzerpMigration;

class UpdatePolinesOverview extends UzerpMigration
{
    public function up()
    {
        $view_name = 'po_linesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.po_linesoverview AS 
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
    u.uom_name,
    pl.mf_workorders_id,
    wo.wo_number || ' - ' || wi.description as workorder,
    pl.mf_operations_id,
    concat(mfo.op_no , ' - '::text, mfo.remarks::text) AS operation
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
    LEFT JOIN po_product_lines_header pph ON pph.id = ppl.productline_header_id
    LEFT JOIN mf_workorders wo on wo.id = pl.mf_workorders_id
    LEFT JOIN st_items wi on wi.id = wo.stitem_id
    LEFT JOIN mf_operations mfo on mfo.id = pl.mf_operations_id
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }


    public function down()
    {
        $view_name = 'po_linesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.po_linesoverview AS 
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
    u.uom_name,
    pl.mf_workorders_id,
    wo.wo_number || ' - ' || wi.description as workorder,
    pl.mf_operations_id,
    mfo.op_no || ' - ' || mfo.remarks as operation
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
    LEFT JOIN po_product_lines_header pph ON pph.id = ppl.productline_header_id
    LEFT JOIN mf_workorders wo on wo.id = pl.mf_workorders_id
    LEFT JOIN st_items wi on wi.id = wo.stitem_id
    LEFT JOIN mf_operations mfo on mfo.id = pl.mf_operations_id
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
