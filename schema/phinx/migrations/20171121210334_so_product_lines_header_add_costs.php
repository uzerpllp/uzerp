<?php

use UzerpPhinx\UzerpMigration;
/**
 * Phinx Migration
 *
 * Update SO Product Header view for sales order product line header costs enhamncement
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 */

class SoProductLinesHeaderAddCosts extends UzerpMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $so_product_lines_header = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW so_productlines_header_overview AS
SELECT plh.id,
    plh.stitem_id,
    plh.stuom_id,
    plh.description,
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

        // Drop and recreate the view
        $this->query('DROP VIEW so_productlines_header_overview');
        $this->query($so_product_lines_header);
        $this->query('ALTER TABLE so_productlines_header_overview OWNER TO "www-data";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $so_product_lines_header = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW so_productlines_header_overview AS
        SELECT plh.id,
        plh.stitem_id,
        plh.stuom_id,
        plh.description,
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
        st.latest_cost,
        st.std_cost,
        uom.uom_name,
        pg.description AS product_group,
        tax.description AS tax_rate,
        gla.account AS gl_account,
        glc.cost_centre AS gl_centre
        FROM so_product_lines_header plh
          LEFT JOIN st_items st ON plh.stitem_id = st.id
          LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
          LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
          JOIN taxrates tax ON plh.tax_rate_id = tax.id
          JOIN gl_accounts gla ON plh.glaccount_id = gla.id
          JOIN gl_centres glc ON plh.glcentre_id = glc.id;
VIEW_WRAP;

        // Drop and recreate the view
        $this->query('DROP VIEW so_productlines_header_overview');
        $this->query($so_product_lines_header);
        $this->query('ALTER TABLE so_productlines_header_overview OWNER TO "www-data";');
    }
}
