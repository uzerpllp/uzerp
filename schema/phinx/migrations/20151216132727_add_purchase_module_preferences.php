<?php
use Phinx\Migration\AbstractMigration;

/**
 * Phinx Migration
 *
 * Add module_component and permission for purchase_order module parameters
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddPurchaseModulePreferences extends AbstractMigration
{

    public function up()
    {
        // Add module_component
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'purchase_order'");
        $module_component_data = array(
            array(
                'name' => 'setupcontroller',
                'type' => 'C',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/order/purchase_order/controllers/SetupController.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Purchase Order Module Preferences'
            )
        );
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();

        // Add permission
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'setupcontroller' AND module_id = {$module['id']}");
        $permission_parent_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'purchase_order' AND title = 'Purchasing Setup' AND module_id = {$module['id']}");

        $permission_data = array(
            array(
                'permission' => 'index',
                'type' => 'a',
                'title' => 'PO Module Preferences',
                'position' => 10,
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

        file_put_contents('php://stderr', 'Permission added for purchase_order module SetupController, please update your user roles.' . PHP_EOL);
    }
}
