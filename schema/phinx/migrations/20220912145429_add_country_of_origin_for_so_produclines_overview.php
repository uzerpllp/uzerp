<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class AddCountryOfOriginForSoProduclinesOverview extends UzerpMigration
{
    /**
     * Add country_of_origin to so_productlines_header_overview
     *
     * @return void
     */
    public function up()
    {
        $view_name = 'so_productlines_header_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW public.so_productlines_header_overview
        AS
        SELECT plh.id,
            plh.stitem_id,
            plh.stuom_id,
            plh.description,
            plh.ean,
            concat_ws(' - ', plh.country_of_origin, countries.name) as countrycode,
            plh.commodity_code,
            plh.glaccount_id,
            plh.glcentre_id,
            plh.tax_rate_id,
            plh.prod_group_id,
            plh.start_date,
            plh.end_date,
            plh.usercompanyid,
            plh.created,
            plh.createdby,
            plh.alteredby,
            plh.lastupdated,
            (st.item_code::text || ' - '::text) || st.description::text AS stitem,
                CASE
                    WHEN plh.stitem_id IS NOT NULL THEN st.latest_cost
                    ELSE soc.cost
                END AS latest_cost,
                CASE
                    WHEN plh.stitem_id IS NOT NULL THEN st.std_cost
                    ELSE soc.cost
                END AS std_cost,
            uom.uom_name,
            pg.description AS product_group,
            tax.description AS tax_rate,
            gla.account AS gl_account,
            glc.cost_centre AS gl_centre,
            soc.id AS soc_id
        FROM so_product_lines_header plh
            LEFT JOIN st_items st ON plh.stitem_id = st.id
            LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
            LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
            LEFT JOIN so_costs soc ON plh.id = soc.product_header_id
            JOIN taxrates tax ON plh.tax_rate_id = tax.id
            JOIN gl_accounts gla ON plh.glaccount_id = gla.id
            JOIN gl_centres glc ON plh.glcentre_id = glc.id
            LEFT JOIN countries ON countries.code = plh.country_of_origin;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'so_productlines_header_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW public.so_productlines_header_overview
        AS
        SELECT plh.id,
            plh.stitem_id,
            plh.stuom_id,
            plh.description,
            plh.ean,
            plh.commodity_code,
            plh.glaccount_id,
            plh.glcentre_id,
            plh.tax_rate_id,
            plh.prod_group_id,
            plh.start_date,
            plh.end_date,
            plh.usercompanyid,
            plh.created,
            plh.createdby,
            plh.alteredby,
            plh.lastupdated,
            (st.item_code::text || ' - '::text) || st.description::text AS stitem,
                CASE
                    WHEN plh.stitem_id IS NOT NULL THEN st.latest_cost
                    ELSE soc.cost
                END AS latest_cost,
                CASE
                    WHEN plh.stitem_id IS NOT NULL THEN st.std_cost
                    ELSE soc.cost
                END AS std_cost,
            uom.uom_name,
            pg.description AS product_group,
            tax.description AS tax_rate,
            gla.account AS gl_account,
            glc.cost_centre AS gl_centre,
            soc.id AS soc_id
        FROM so_product_lines_header plh
            LEFT JOIN st_items st ON plh.stitem_id = st.id
            LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
            LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
            LEFT JOIN so_costs soc ON plh.id = soc.product_header_id
            JOIN taxrates tax ON plh.tax_rate_id = tax.id
            JOIN gl_accounts gla ON plh.glaccount_id = gla.id
            JOIN gl_centres glc ON plh.glcentre_id = glc.id;
VIEW_WRAP;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
