<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class AddCountryOfOriginForSilinesOverview extends UzerpMigration
{
    /**
     * Add country of origin to si_linesoverview
     *
     * @return void
     */
    public function up()
    {
        $view_name = 'si_linesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW public.si_linesoverview
        AS
        SELECT sl.id,
            sl.invoice_id,
            sl.line_number,
            sl.sales_order_id,
            sl.order_line_id,
            sl.stitem_id,
            sl.item_description,
            sph.ean,
            concat_ws(' - ', sph.country_of_origin, countries.name) as country_of_origin,
            sl.sales_qty,
            sl.sales_price,
            sl.currency_id,
            sl.rate,
            sl.gross_value,
            sl.tax_value,
            sl.net_value,
            sl.twin_currency_id,
            sl.twin_rate,
            sl.twin_gross_value,
            sl.twin_tax_value,
            sl.twin_net_value,
            sl.base_gross_value,
            sl.base_tax_value,
            sl.base_net_value,
            sl.glaccount_id,
            sl.glcentre_id,
            sl.description,
            sl.usercompanyid,
            sl.line_discount,
            sl.tax_rate_id,
            sph.commodity_code,
            sl.delivery_note,
            sl.stuom_id,
            sl.created,
            sl.createdby,
            sl.alteredby,
            sl.lastupdated,
            sl.productline_id,
            sl.move_stock,
            sl.tax_rate_percent,
            sl.glaccount_centre_id,
            sh.invoice_date,
            sh.invoice_number,
            sh.transaction_type,
            sh.slmaster_id,
            sh.status,
            soh.order_number,
            c.name AS customer,
            i.item_code,
            (i.item_code::text || ' - '::text) || i.description::text AS stitem,
            uom.uom_name,
            spl.customer_product_code
            FROM si_lines sl
            JOIN si_header sh ON sh.id = sl.invoice_id
            JOIN slmaster slm ON sh.slmaster_id = slm.id
            JOIN company c ON slm.company_id = c.id
            LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
            LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
            LEFT JOIN st_items i ON i.id = sl.stitem_id
            LEFT JOIN so_product_lines spl ON spl.id = sl.productline_id
            LEFT JOIN so_product_lines_header sph ON sph.id = spl.productline_header_id
            LEFT JOIN countries ON countries.code = sph.country_of_origin;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'si_linesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW public.si_linesoverview
        AS
        SELECT sl.id,
            sl.invoice_id,
            sl.line_number,
            sl.sales_order_id,
            sl.order_line_id,
            sl.stitem_id,
            sl.item_description,
            sph.ean,
            sl.sales_qty,
            sl.sales_price,
            sl.currency_id,
            sl.rate,
            sl.gross_value,
            sl.tax_value,
            sl.net_value,
            sl.twin_currency_id,
            sl.twin_rate,
            sl.twin_gross_value,
            sl.twin_tax_value,
            sl.twin_net_value,
            sl.base_gross_value,
            sl.base_tax_value,
            sl.base_net_value,
            sl.glaccount_id,
            sl.glcentre_id,
            sl.description,
            sl.usercompanyid,
            sl.line_discount,
            sl.tax_rate_id,
            sph.commodity_code,
            sl.delivery_note,
            sl.stuom_id,
            sl.created,
            sl.createdby,
            sl.alteredby,
            sl.lastupdated,
            sl.productline_id,
            sl.move_stock,
            sl.tax_rate_percent,
            sl.glaccount_centre_id,
            sh.invoice_date,
            sh.invoice_number,
            sh.transaction_type,
            sh.slmaster_id,
            sh.status,
            soh.order_number,
            c.name AS customer,
            i.item_code,
            (i.item_code::text || ' - '::text) || i.description::text AS stitem,
            uom.uom_name,
            spl.customer_product_code
            FROM si_lines sl
            JOIN si_header sh ON sh.id = sl.invoice_id
            JOIN slmaster slm ON sh.slmaster_id = slm.id
            JOIN company c ON slm.company_id = c.id
            LEFT JOIN so_header soh ON sl.sales_order_id = soh.id
            LEFT JOIN st_uoms uom ON sl.stuom_id = uom.id
            LEFT JOIN st_items i ON i.id = sl.stitem_id
            LEFT JOIN so_product_lines spl ON spl.id = sl.productline_id
            LEFT JOIN so_product_lines_header sph ON sph.id = spl.productline_header_id;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
