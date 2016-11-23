<?php

use UzerpPhinx\UzerpMigration;

class CustomerServiceViewAddCsnote extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][customer_service]'
    );

    /*
     * Add cs_failure_note to view
     */
    public function up()
    {
        $cs_view = <<<'VIEW'
CREATE OR REPLACE VIEW customer_service AS
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
    sd.cs_failure_note,
    sh.order_number,
    sl.due_despatch_date,
    sl.order_qty,
    st.prod_group_id,
    pg.product_group,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    c.name AS customer,
    cs.code AS failurecode,
    cs.description AS failure_description
   FROM so_despatchlines sd
     JOIN slmaster sc ON sc.id = sd.slmaster_id
     JOIN company c ON sc.company_id = c.id
     JOIN so_lines sl ON sl.id = sd.orderline_id
     JOIN so_header sh ON sh.id = sl.order_id
     LEFT JOIN cs_failurecodes cs ON cs.id = sd.cs_failurecode_id
     JOIN st_items st ON st.id = sd.stitem_id
     JOIN st_productgroups pg ON pg.id = st.prod_group_id
  WHERE sd.status::text = 'D'::text;
VIEW;

        $this->query("select deps_save_and_drop_dependencies('public', 'customer_service')");
        $this->query('DROP VIEW customer_service');
        $this->query($cs_view);
        $this->query('ALTER TABLE customer_service OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'customer_service')");
        $this->cleanMemcache($this->cache_keys);
    }

    public function down()
    {
        $cs_view = <<<'VIEW'
CREATE OR REPLACE VIEW customer_service AS
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
    sh.order_number,
    sl.due_despatch_date,
    sl.order_qty,
    st.prod_group_id,
    pg.product_group,
    (st.item_code::text || ' - '::text) || st.description::text AS stitem,
    c.name AS customer,
    cs.code AS failurecode,
    cs.description AS failure_description
   FROM so_despatchlines sd
     JOIN slmaster sc ON sc.id = sd.slmaster_id
     JOIN company c ON sc.company_id = c.id
     JOIN so_lines sl ON sl.id = sd.orderline_id
     JOIN so_header sh ON sh.id = sl.order_id
     LEFT JOIN cs_failurecodes cs ON cs.id = sd.cs_failurecode_id
     JOIN st_items st ON st.id = sd.stitem_id
     JOIN st_productgroups pg ON pg.id = st.prod_group_id
  WHERE sd.status::text = 'D'::text;
VIEW;

        $this->query("select deps_save_and_drop_dependencies('public', 'customer_service')");
        $this->query('DROP VIEW customer_service');
        $this->query($cs_view);
        $this->query('ALTER TABLE customer_service OWNER TO "www-data"');
        $this->query("select deps_restore_dependencies('public', 'customer_service')");
        $this->cleanMemcache($this->cache_keys);
    }
}
