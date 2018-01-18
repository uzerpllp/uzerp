<?php
use Phinx\Migration\AbstractMigration;


/**
 * Phinx Migration
 *
 * Add module_component and permission for manufacturing module preferences
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddManufactringSettings extends AbstractMigration
{
    public function up()
    {
        // Add module_component
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'manufacturing'");
        $module_component_data = array(
            array(
                'name' => 'setupcontroller',
                'type' => 'C',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/manufacturing/controllers/SetupController.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Manufacturing Module Preferences'
            )
        );
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();

        // Add permission
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'setupcontroller' AND module_id = ${module['id']}");
        $permission_root_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'manufacturing_setup' AND title = 'Manufacturing Setup'");

        $table = $this->table('permissions');

        $permission_data = array(
            array(
                'permission' => 'index',
                'type' => 'a',
                'title' => 'MF Module Preferences',
                'position' => 10,
                'display' => 'true',
                'parent_id' => $permission_root_id['id'],
                'createdby' => 'admin',
                'module_id' => $module['id'],
                'component_id' => $module_component_id['id']
            )
        );


        $table->insert($permission_data);
        $table->save();

        file_put_contents('php://stderr', 'Permission added for sales_order module SetupController, please update your user roles.' . PHP_EOL);
    }
}
