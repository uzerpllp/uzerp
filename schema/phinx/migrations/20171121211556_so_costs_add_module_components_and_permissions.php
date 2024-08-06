<?php

use UzerpPhinx\UzerpMigration;
/**
 * Phinx Migration
 *
 * Add module_components and permission for sales order product line header costs enhamncement
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2000-2017 uzERP LLP (support@uzerp.com). All rights reserved.
 */

class SoCostsAddModuleComponentsAndPermissions extends UzerpMigration
{
    public function up()
    {
        // Add module_component rows
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'costing'");
        //var_dump( $module);
        $module_component_data = array(
            array(
                'name' => 'socostscontroller',
                'type' => 'C',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/costing/controllers/SocostsController.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Sales Order Product Header Costs Controller'
            ),
            array(
                'name' => 'socost',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/costing/models/SOCost.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Sales Order Product Header Costs Model'
            ),
            array(
                'name' => 'socostcollection',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/costing/models/SOCostCollection.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Sales Order Product Header Costs Collection'
            )
        );
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();

        // Add permission
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'socostscontroller' AND module_id = {$module['id']}");
        $permission_parent_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'costing' AND title = 'Costing' AND module_id = {$module['id']}");

        $permission_data = array(
            array(
                'permission' => 'socosts',
                'type' => 'c',
                'title' => 'SO Product Costs',
                'position' => 2,
                'display' => TRUE,
                'parent_id' => $permission_parent_id['id'],
                'createdby' => 'admin',
                'module_id' => $module['id'],
                'component_id' => $module_component_id['id']
            )
        );

        $table = $this->table('permissions');
        $table->insert($permission_data);
        $table->save();

        file_put_contents('php://stderr', 'Module Components and Permission added for Sales Order Product costs, please update your user roles.' . PHP_EOL);
    }
}
