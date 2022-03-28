<?php


use UzerpPhinx\UzerpMigration;

class SoLinesOverviewAddCustomerProductCode extends UzerpMigration
{
    public function up()
    {
        $view_name = 'so_linesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
    CREATE OR REPLACE VIEW public.so_linesoverview
    AS
    SELECT sl.id,
        sl.order_id,
        sl.line_number,
        sl.productline_id,
        sph.not_despatchable,
        sl.stuom_id,
        sl.item_description,
        sl.order_qty,
        sl.price,
        sl.currency_id,
        sl.rate,
        sl.net_value,
        sl.twin_currency_id,
        sl.twin_rate,
        sl.twin_net_value,
        sl.base_net_value,
        sl.glaccount_id,
        sl.glcentre_id,
        sl.line_discount,
        sl.os_qty,
        sl.revised_qty,
        sl.del_qty,
        sl.due_delivery_date,
        sl.due_despatch_date,
        sl.actual_despatch_date,
        sl.delivery_note,
        sl.status,
        sl.usercompanyid,
        sl.stitem_id,
        sph.ean,
        sl.tax_rate_id,
        sph.commodity_code,
        sl.created,
        sl.createdby,
        sl.alteredby,
        sl.lastupdated,
        sl.line_value,
        sl.line_tradedisc_percentage,
        sl.line_qtydisc_percentage,
        sl.description,
        sl.note,
        sl.external_data,
        sl.glaccount_centre_id,
        sh.due_date,
        sh.order_date,
        sh.order_number,
        sh.slmaster_id,
        sh.type,
        c.name AS customer,
        (gla.account::text || ' - '::text) || gla.description::text AS glaccount,
        (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre,
        tax.description AS taxrate,
        (i.item_code::text || ' - '::text) || i.description::text AS stitem,
        i.item_code,
        uom.uom_name,
        spl.customer_product_code
        FROM so_lines sl
        JOIN so_header sh ON sh.id = sl.order_id
        JOIN slmaster slm ON sh.slmaster_id = slm.id
        JOIN company c ON slm.company_id = c.id
        JOIN taxrates tax ON sl.tax_rate_id = tax.id
        JOIN gl_accounts gla ON sl.glaccount_id = gla.id
        JOIN gl_centres glc ON sl.glcentre_id = glc.id
        LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
        LEFT JOIN st_items i ON i.id = sl.stitem_id
        LEFT JOIN so_product_lines spl ON spl.id = sl.productline_id
        LEFT JOIN so_product_lines_header sph ON sph.id = spl.productline_header_id;
    VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'view_name';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
    CREATE OR REPLACE VIEW public.so_linesoverview
    AS
    SELECT sl.id,
    sl.order_id,
    sl.line_number,
    sl.productline_id,
    sph.not_despatchable,
    sl.stuom_id,
    sl.item_description,
    sl.order_qty,
    sl.price,
    sl.currency_id,
    sl.rate,
    sl.net_value,
    sl.twin_currency_id,
    sl.twin_rate,
    sl.twin_net_value,
    sl.base_net_value,
    sl.glaccount_id,
    sl.glcentre_id,
    sl.line_discount,
    sl.os_qty,
    sl.revised_qty,
    sl.del_qty,
    sl.due_delivery_date,
    sl.due_despatch_date,
    sl.actual_despatch_date,
    sl.delivery_note,
    sl.status,
    sl.usercompanyid,
    sl.stitem_id,
    sph.ean,
    sl.tax_rate_id,
    sph.commodity_code,
    sl.created,
    sl.createdby,
    sl.alteredby,
    sl.lastupdated,
    sl.line_value,
    sl.line_tradedisc_percentage,
    sl.line_qtydisc_percentage,
    sl.description,
    sl.note,
    sl.external_data,
    sl.glaccount_centre_id,
    sh.due_date,
    sh.order_date,
    sh.order_number,
    sh.slmaster_id,
    sh.type,
    c.name AS customer,
    (gla.account::text || ' - '::text) || gla.description::text AS glaccount,
    (glc.cost_centre::text || ' - '::text) || glc.description::text AS glcentre,
    tax.description AS taxrate,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    i.item_code,
    uom.uom_name
    FROM so_lines sl
    JOIN so_header sh ON sh.id = sl.order_id
    JOIN slmaster slm ON sh.slmaster_id = slm.id
    JOIN company c ON slm.company_id = c.id
    JOIN taxrates tax ON sl.tax_rate_id = tax.id
    JOIN gl_accounts gla ON sl.glaccount_id = gla.id
    JOIN gl_centres glc ON sl.glcentre_id = glc.id
    LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
    LEFT JOIN st_items i ON i.id = sl.stitem_id
    LEFT JOIN so_product_lines spl ON spl.id = sl.productline_id
    LEFT JOIN so_product_lines_header sph ON sph.id = spl.productline_header_id;
    VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
