<?php

use UzerpPhinx\UzerpMigration;

class SoLinesOverviewDespatchSelect extends UzerpMigration
{

    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_linesoverview]'
    );
    
    
    /**
     * Add field not_despatchable to so_linesoverview
     */
    public function up()
    {
        $so_linesoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW so_linesoverview AS
 SELECT sl.id,
    sl.order_id,
    sl.line_number,
    sl.productline_id,
    spl.not_despatchable,
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
    sl.tax_rate_id,
    sl.created,
    sl.createdby,
    sl.alteredby,
    sl.lastupdated,
    sl.line_value,
    sl.line_tradedisc_percentage,
    sl.line_qtydisc_percentage,
    sl.description,
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
     LEFT JOIN so_productlines_overview spl ON spl.id = sl.productline_id;
VIEW_WRAP;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'so_linesoverview')");
        $this->query('DROP VIEW so_linesoverview');
        $this->query($so_linesoverview);
        $this->query('ALTER TABLE so_linesoverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_linesoverview')");
        $this->cleanMemcache($this->cache_keys);
    }
    
    public function down()
    {
        $so_linesoverview = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW so_linesoverview AS
 SELECT sl.id,
    sl.order_id,
    sl.line_number,
    sl.productline_id,
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
    sl.tax_rate_id,
    sl.created,
    sl.createdby,
    sl.alteredby,
    sl.lastupdated,
    sl.line_value,
    sl.line_tradedisc_percentage,
    sl.line_qtydisc_percentage,
    sl.description,
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
VIEW_WRAP;
    
        $this->query("select deps_save_and_drop_dependencies('public', 'so_linesoverview')");
        $this->query('DROP VIEW so_linesoverview');
        $this->query($so_linesoverview);
        $this->query('ALTER TABLE so_linesoverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_linesoverview')");
        $this->cleanMemcache($this->cache_keys);
    }
}
