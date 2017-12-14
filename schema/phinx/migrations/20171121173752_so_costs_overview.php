<?php

use UzerpPhinx\UzerpMigration;
/**
 * Phinx Migration
 *
 * Create SO Costs view for sales order product line header costs enhamncement
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 */
//CREATE OR REPLACE VIEW so_costsoverview AS

class SoCostsOverview extends UzerpMigration
{
     public function up()
     {
         $view_name = 'so_costsoverview';
         $view_owner = 'www-data';
         $view = <<<VIEW
CREATE VIEW so_costsoverview AS
SELECT c.id,
c.product_header_id,
c.cost,
c.mat,
c.lab,
c.osc,
c.ohd,
c."time",
c.time_period,
c.lastupdated,
c.alteredby,
c.usercompanyid,
ph.description AS soproduct
FROM so_costs c
JOIN so_product_lines_header ph ON c.product_header_id = ph.id
VIEW;

         $this->query($view);
         $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
     }
 }
