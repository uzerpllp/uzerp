<?php
/**
 * Phinx migration - po_product_orders_add_project_id
 * 
 * Adds project_id field to the view public.po_product_orders
 * 
 * @author Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later; See LICENSE
 * 
 * @since 1.4.1
 */

use Phinx\Migration\AbstractMigration;

class PoProductOrdersAddProjectId extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {

        $po_product_orders = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_product_orders AS 
 SELECT ph.id,
    ph.order_number,
    ph.plmaster_id,
    ph.del_address_id,
    ph.order_date,
    ph.due_date,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_net_value,
    ph.base_net_value,
    ph.type,
    ph.status,
    ph.description,
    ph.usercompanyid,
    ph.date_authorised,
    ph.raised_by,
    ph.authorised_by,
    ph.created,
    ph.owner,
    ph.lastupdated,
    ph.alteredby,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin_currency,
    ph.raised_by_person,
    ph.authorised_by_person,
    ph.project,
    ph.project_id,
    pl.id AS orderline_id,
    pl.productline_id,
    pl.status AS line_status,
    ppl.productline_header_id
   FROM po_headeroverview ph
     JOIN po_lines pl ON ph.id = pl.order_id
     JOIN po_product_lines ppl ON ppl.id = pl.productline_id;
VIEW_WRAP;

        // Drop and recreate the view
        $this->query('DROP VIEW po_product_orders');
        $this->query($po_product_orders);
        $this->query('ALTER TABLE po_product_orders OWNER TO "www-data";');
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $po_product_orders = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW po_product_orders AS 
 SELECT ph.id,
    ph.order_number,
    ph.plmaster_id,
    ph.del_address_id,
    ph.order_date,
    ph.due_date,
    ph.ext_reference,
    ph.currency_id,
    ph.rate,
    ph.net_value,
    ph.twin_currency_id,
    ph.twin_rate,
    ph.twin_net_value,
    ph.base_net_value,
    ph.type,
    ph.status,
    ph.description,
    ph.usercompanyid,
    ph.date_authorised,
    ph.raised_by,
    ph.authorised_by,
    ph.created,
    ph.owner,
    ph.lastupdated,
    ph.alteredby,
    ph.payee_name,
    ph.supplier,
    ph.currency,
    ph.twin_currency,
    ph.raised_by_person,
    ph.authorised_by_person,
    ph.project,
    pl.id AS orderline_id,
    pl.productline_id,
    pl.status AS line_status,
    ppl.productline_header_id
   FROM po_headeroverview ph
     JOIN po_lines pl ON ph.id = pl.order_id
     JOIN po_product_lines ppl ON ppl.id = pl.productline_id;
VIEW_WRAP;

        // Drop and recreate the view
        $this->query('DROP VIEW po_product_orders');
        $this->query($po_product_orders);
        $this->query('ALTER TABLE po_product_orders OWNER TO "www-data";');
    }
}
