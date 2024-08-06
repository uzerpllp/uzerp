<?php
use UzerpPhinx\UzerpMigration;

class SoItemOrdersNonstock extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_itemorders]'
    );

    /**
     * Include order lines that are not linked to stock via productlines
     *
     * Exclude order lines where there is no productline (manually entered)
     * OR the productline is marked as not_despatchable
     */
    public function up()
    {
        $so_itemorders = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW so_itemorders AS
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
    sh.despatch_date,
    sh.customer,
    sh.order_number,
    sh.slmaster_id,
    sh.type,
    sh.account_status,
    sh.despatch_action,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    sl.revised_qty AS required,
    u.uom_name AS stuom
   FROM so_lines sl
     LEFT JOIN st_items i ON i.id = sl.stitem_id
     JOIN st_uoms u ON u.id = sl.stuom_id
     JOIN so_headeroverview sh ON sh.id = sl.order_id
     JOIN so_productlines_overview spl ON spl.id = sl.productline_id
  WHERE sl.productline_id IS NOT NULL AND spl.not_despatchable IS NOT TRUE AND (sl.status::text = ANY (ARRAY['N'::character varying::text, 'R'::character varying::text]));
VIEW_WRAP;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'so_itemorders')");
        $this->query('DROP VIEW so_itemorders');
        $this->query($so_itemorders);
        $this->query('ALTER TABLE so_itemorders OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_itemorders')");
        $this->cleanMemcache($this->cache_keys);
    }

    public function down()
    {
        $so_itemorders = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW so_itemorders AS
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
    sh.despatch_date,
    sh.customer,
    sh.order_number,
    sh.slmaster_id,
    sh.type,
    sh.account_status,
    sh.despatch_action,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    sl.revised_qty AS required,
    u.uom_name AS stuom
   FROM so_lines sl
     JOIN st_items i ON i.id = sl.stitem_id
     JOIN st_uoms u ON u.id = sl.stuom_id
     JOIN so_headeroverview sh ON sh.id = sl.order_id
  WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::character varying::text, 'R'::character varying::text]));
VIEW_WRAP;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'so_itemorders')");
        $this->query('DROP VIEW so_itemorders');
        $this->query($so_itemorders);
        $this->query('ALTER TABLE so_itemorders OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_itemorders')");
        $this->cleanMemcache($this->cache_keys);
    }
}
