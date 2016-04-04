<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration
 *
 * Add project_id from so_header to so_product_orders view
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class SoProductOrdersAddProjectId extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[table_fields][so_product_orders]'
    );

    /*
     * Add project_id to view
     */
    public function up()
    {
        $so_product_orders = <<<'VIEW'
CREATE OR REPLACE VIEW so_product_orders AS
 SELECT sh.id,
    sh.order_number,
    sh.slmaster_id,
    sh.del_address_id,
    sh.order_date,
    sh.due_date,
    sh.despatch_date,
    sh.ext_reference,
    sh.currency_id,
    sh.rate,
    sh.net_value,
    sh.twin_currency_id,
    sh.twin_rate,
    sh.twin_net_value,
    sh.base_net_value,
    sh.type,
    sh.status,
    sh.description,
    sh.usercompanyid,
    sh.despatch_action,
    sh.inv_address_id,
    sh.created,
    sh.createdby,
    sh.alteredby,
    sh.lastupdated,
    sh.person_id,
    sh.account_status,
    sh.customer,
    sh.currency,
    sh.twin_currency,
    sh.person,
    sh.project,
    sh.project_id,
    sl.id AS orderline_id,
    sl.productline_id,
    sl.status AS line_status,
    spl.productline_header_id
   FROM so_headeroverview sh
     JOIN so_lines sl ON sh.id = sl.order_id
     JOIN so_product_lines spl ON spl.id = sl.productline_id;
VIEW;

        $this->query('DROP VIEW so_product_orders');
        $this->query($so_product_orders);
        $this->query('ALTER TABLE so_product_orders OWNER TO "www-data"');
        $this->cleanMemcache($this->cache_keys);
    }

    /*
     * Remove project_id from view
     */
    public function down()
    {
        $so_product_orders = <<<'VIEW'
CREATE OR REPLACE VIEW so_product_orders AS
 SELECT sh.id,
    sh.order_number,
    sh.slmaster_id,
    sh.del_address_id,
    sh.order_date,
    sh.due_date,
    sh.despatch_date,
    sh.ext_reference,
    sh.currency_id,
    sh.rate,
    sh.net_value,
    sh.twin_currency_id,
    sh.twin_rate,
    sh.twin_net_value,
    sh.base_net_value,
    sh.type,
    sh.status,
    sh.description,
    sh.usercompanyid,
    sh.despatch_action,
    sh.inv_address_id,
    sh.created,
    sh.createdby,
    sh.alteredby,
    sh.lastupdated,
    sh.person_id,
    sh.account_status,
    sh.customer,
    sh.currency,
    sh.twin_currency,
    sh.person,
    sl.id AS orderline_id,
    sl.productline_id,
    sl.status AS line_status,
    spl.productline_header_id
   FROM so_headeroverview sh
     JOIN so_lines sl ON sh.id = sl.order_id
     JOIN so_product_lines spl ON spl.id = sl.productline_id;
VIEW;

        $this->query('DROP VIEW so_product_orders');
        $this->query($so_product_orders);
        $this->query('ALTER TABLE so_product_orders OWNER TO "www-data"');
        $this->cleanMemcache($this->cache_keys);
    }
}

