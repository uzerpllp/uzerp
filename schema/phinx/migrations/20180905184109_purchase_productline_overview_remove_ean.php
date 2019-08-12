<?php


use UzerpPhinx\UzerpMigration;

class PurchaseProductlineOverviewRemoveEan extends UzerpMigration
{
    /**
     * Remove EAN
     */
    public function up()
    {
        $view = <<<VIEW
CREATE OR REPLACE VIEW po_productlines_overview AS 
SELECT pl.id,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.plmaster_id,
    pl.supplier_product_code,
    pl.description,
    pl.price,
    pl.usercompanyid,
    pl.currency_id,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.start_date,
    pl.end_date,
    pl.productline_header_id,
    plh.description AS product,
    plh.stitem_id,
    plh.stuom_id,
    plh.tax_rate_id,
    plh.prod_group_id,
    plm.payee_name,
    c.name AS supplier,
    uom.uom_name,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    st.comp_class,
    gla.account AS glaccount,
    glc.cost_centre AS glcentre,
    cur.currency,
    pg.description AS stproductgroup
    FROM po_product_lines pl
    JOIN po_product_lines_header plh ON pl.productline_header_id = plh.id
    LEFT JOIN plmaster plm ON pl.plmaster_id = plm.id
    LEFT JOIN company c ON plm.company_id = c.id
    LEFT JOIN st_items st ON plh.stitem_id = st.id
    LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
    LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
    JOIN cumaster cur ON pl.currency_id = cur.id
    JOIN gl_accounts gla ON pl.glaccount_id = gla.id
    JOIN gl_centres glc ON pl.glcentre_id = glc.id;
VIEW;

        $viewname = 'po_productlines_overview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }

    /**
     * Add EAN
     */
    public function down()
    {
        $view = <<<VIEW
CREATE OR REPLACE VIEW po_productlines_overview AS 
SELECT pl.id,
    pl.glaccount_id,
    pl.glcentre_id,
    pl.plmaster_id,
    pl.supplier_product_code,
    pl.description,
    pl.price,
    pl.usercompanyid,
    pl.currency_id,
    pl.created,
    pl.createdby,
    pl.alteredby,
    pl.lastupdated,
    pl.start_date,
    pl.end_date,
    pl.ean,
    pl.productline_header_id,
    plh.description AS product,
    plh.stitem_id,
    plh.stuom_id,
    plh.tax_rate_id,
    plh.prod_group_id,
    plm.payee_name,
    c.name AS supplier,
    uom.uom_name,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    st.comp_class,
    gla.account AS glaccount,
    glc.cost_centre AS glcentre,
    cur.currency,
    pg.description AS stproductgroup
    FROM po_product_lines pl
    JOIN po_product_lines_header plh ON pl.productline_header_id = plh.id
    LEFT JOIN plmaster plm ON pl.plmaster_id = plm.id
    LEFT JOIN company c ON plm.company_id = c.id
    LEFT JOIN st_items st ON plh.stitem_id = st.id
    LEFT JOIN st_uoms uom ON plh.stuom_id = uom.id
    LEFT JOIN st_productgroups pg ON plh.prod_group_id = pg.id
    JOIN cumaster cur ON pl.currency_id = cur.id
    JOIN gl_accounts gla ON pl.glaccount_id = gla.id
    JOIN gl_centres glc ON pl.glcentre_id = glc.id;
VIEW;

        $viewname = 'po_productlines_overview';
        $this->query("select deps_save_and_drop_dependencies('public', '{$viewname}')");
        $this->query("DROP VIEW {$viewname}");
        $this->query($view);
        $this->query("ALTER TABLE {$viewname}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$viewname}')");
    }
}
