<?php
use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration
 *
 * Add module_component and permission for sales_order module parameters
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddSorderModulePreferences extends UzerpMigration
{
    // Cache keys to be cleaned on migration/rollback
    protected $cache_keys = array(
        '[resources][lib_root]'
    );

    public function up()
    {
        // Add module_component
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'sales_order'");
        $module_component_data = array(
            array(
                'name' => 'setupcontroller',
                'type' => 'C',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/order/sales_order/controllers/SetupController.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Sales Order Module Preferences'
            )
        );
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();

        // Add permission
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'setupcontroller' AND module_id = ${module['id']}");
        $permission_root_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'logistics' AND title = 'Logistics Setup'");

        $table = $this->table('permissions');

        $group_permission_data = array(
            array(
                'permission' => 'sales_order',
                'type' => 'g',
                'title' => 'Sales Setup',
                'position' => 10,
                'display' => 'true',
                'parent_id' => $permission_root_id['id'],
                'createdby' => 'admin',
                'module_id' => $module['id'],
            )
        );

        $table->insert($group_permission_data);
        $table->save();

        $group_permission = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'sales_order' AND title = 'Sales Setup'");

        $permission_data = array(
            array(
                'permission' => 'index',
                'type' => 'a',
                'title' => 'SO Module Preferences',
                'position' => 10,
                'display' => 'true',
                'parent_id' => $group_permission['id'],
                'createdby' => 'admin',
                'module_id' => $module['id'],
                'component_id' => $module_component_id['id']
            )
        );


        $table->insert($permission_data);
        $table->save();

        // Force refresh of autloader cache for uzERP libs
        // because we've added lib/classes/traits/SOactionAllowedOnStop that uses the SO module prefs
        $this->cleanMemcache($this->cache_keys);

        file_put_contents('php://stderr', 'Permission added for sales_order module SetupController, please update your user roles.' . PHP_EOL);
    }
}
