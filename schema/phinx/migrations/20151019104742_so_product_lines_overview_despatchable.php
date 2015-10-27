<?php
use UzerpPhinx\UzerpMigration;

class SoProductLinesOverviewDespatchable extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_productlines_overview]'
    );

    /**
     * Add field not_despatchable to so_productlines_overview
     *
     * This is a flag to indicate that a product's lines should
     * not be available to release for despatch
     */
    public function up()
    {
        $so_productlines_overview = <<<'VIEW'
CREATE OR REPLACE VIEW so_productlines_overview AS
 SELECT pl.id,
    pl.currency_id,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.slmaster_id,
    pl.customer_product_code,
    pl.description,
    pl.price,
    pl.usercompanyid,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.so_price_type_id,
    pl.start_date,
    pl.end_date,
    pl.productline_header_id,
    plh.description AS product,
    plh.stitem_id,
    plh.stuom_id,
    plh.tax_rate_id,
    plh.prod_group_id,
    plh.not_despatchable,
    c.name AS customer,
    uom.uom_name,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    gla.account AS glaccount,
    glc.cost_centre AS glcentre,
    cu.currency,
    tax.description AS taxrate,
    pt.name AS so_price_type,
    (pg.product_group::text || ' - '::text) || pg.description::text AS stproductgroup
   FROM so_product_lines pl
     JOIN so_product_lines_header plh ON pl.productline_header_id = plh.id
     LEFT JOIN slmaster slm ON pl.slmaster_id = slm.id
     LEFT JOIN company c ON slm.company_id = c.id
     LEFT JOIN st_items st ON plh.stitem_id = st.id
     LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
     LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
     JOIN cumaster cu ON pl.currency_id = cu.id
     JOIN taxrates tax ON plh.tax_rate_id = tax.id
     JOIN gl_accounts gla ON pl.glaccount_id = gla.id
     JOIN gl_centres glc ON pl.glcentre_id = glc.id
     LEFT JOIN so_price_types pt ON pl.so_price_type_id = pt.id;
VIEW;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'so_productlines_overview')");
        $this->query('DROP VIEW so_productlines_overview');
        $this->query($so_productlines_overview);
        $this->query('ALTER TABLE so_productlines_overview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_productlines_overview')");
        $this->cleanMemcache($this->cache_keys);
    }

    public function down()
    {
        $so_productlines_overview = <<<'VIEW'
CREATE OR REPLACE VIEW so_productlines_overview AS
 SELECT pl.id,
    pl.currency_id,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.slmaster_id,
    pl.customer_product_code,
    pl.description,
    pl.price,
    pl.usercompanyid,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.so_price_type_id,
    pl.start_date,
    pl.end_date,
    pl.productline_header_id,
    plh.description AS product,
    plh.stitem_id,
    plh.stuom_id,
    plh.tax_rate_id,
    plh.prod_group_id,
    c.name AS customer,
    uom.uom_name,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    gla.account AS glaccount,
    glc.cost_centre AS glcentre,
    cu.currency,
    tax.description AS taxrate,
    pt.name AS so_price_type,
    (pg.product_group::text || ' - '::text) || pg.description::text AS stproductgroup
   FROM so_product_lines pl
     JOIN so_product_lines_header plh ON pl.productline_header_id = plh.id
     LEFT JOIN slmaster slm ON pl.slmaster_id = slm.id
     LEFT JOIN company c ON slm.company_id = c.id
     LEFT JOIN st_items st ON plh.stitem_id = st.id
     LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
     LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
     JOIN cumaster cu ON pl.currency_id = cu.id
     JOIN taxrates tax ON plh.tax_rate_id = tax.id
     JOIN gl_accounts gla ON pl.glaccount_id = gla.id
     JOIN gl_centres glc ON pl.glcentre_id = glc.id
     LEFT JOIN so_price_types pt ON pl.so_price_type_id = pt.id;
VIEW;
    }
}