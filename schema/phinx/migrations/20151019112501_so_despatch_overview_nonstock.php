<?php

use UzerpPhinx\UzerpMigration;

class SoDespatchOverviewNonstock extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_despatchoverview]'
    );
    
    /*
     * Add order line description to view
     */
    public function up()
    {
        $so_despatchoverview = <<<'VIEW'
CREATE OR REPLACE VIEW so_despatchoverview AS
 SELECT sd.id,
    sd.despatch_number,
    sd.order_id,
    sd.slmaster_id,
    sd.despatch_date,
    sd.despatch_qty,
    sd.orderline_id,
    sd.productline_id,
    sd.stuom_id,
    sd.stitem_id,
    sd.status,
    sd.usercompanyid,
    sd.cs_failurecode_id,
    sd.invoice_number,
    sd.invoice_id,
    sd.despatch_action,
    c.name AS customer,
    sh.order_number,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    u.uom_name,
    sol.description
   FROM so_despatchlines sd
     LEFT JOIN st_items i ON i.id = sd.stitem_id
     JOIN so_header sh ON sh.id = sd.order_id
     JOIN slmaster sl ON sl.id = sd.slmaster_id
     JOIN so_lines sol ON sd.productline_id = sol.productline_id
     JOIN company c ON sl.company_id = c.id
     JOIN st_uoms u ON u.id = sd.stuom_id;
VIEW;
        
        $this->query("select deps_save_and_drop_dependencies('public', 'so_despatchoverview')");
        $this->query('DROP VIEW so_despatchoverview');
        $this->query($so_despatchoverview);
        $this->query('ALTER TABLE so_despatchoverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_despatchoverview')");
        $this->cleanMemcache($this->cache_keys);
    }
    
    public function down()
    {
        $so_despatchoverview = <<<'VIEW'
CREATE OR REPLACE VIEW so_despatchoverview AS
 SELECT sd.id,
    sd.despatch_number,
    sd.order_id,
    sd.slmaster_id,
    sd.despatch_date,
    sd.despatch_qty,
    sd.orderline_id,
    sd.productline_id,
    sd.stuom_id,
    sd.stitem_id,
    sd.status,
    sd.usercompanyid,
    sd.cs_failurecode_id,
    sd.invoice_number,
    sd.invoice_id,
    sd.despatch_action,
    c.name AS customer,
    sh.order_number,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    u.uom_name
   FROM so_despatchlines sd
     LEFT JOIN st_items i ON i.id = sd.stitem_id
     JOIN so_header sh ON sh.id = sd.order_id
     JOIN slmaster sl ON sl.id = sd.slmaster_id
     JOIN company c ON sl.company_id = c.id
     JOIN st_uoms u ON u.id = sd.stuom_id;
VIEW;
    
        $this->query("select deps_save_and_drop_dependencies('public', 'so_despatchoverview')");
        $this->query('DROP VIEW so_despatchoverview');
        $this->query($so_despatchoverview);
        $this->query('ALTER TABLE so_despatchoverview OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'so_despatchoverview')");
        $this->cleanMemcache($this->cache_keys);
    }
    
    
}
