<?php
/**
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */

use UzerpPhinx\UzerpMigration;

class PermissionReviewPlannedPurchases extends UzerpMigration
{
    /**
     * Add permission for plannned purchase order review
     */
    public function up() {
        $module_id = $this->fetchRow("SELECT id FROM modules WHERE name = 'purchase_order'");
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'poplannedcontroller'");
        $parent_group_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'logisitics'");
        $permission_parent_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'purchase_order' AND type = 'm' AND parent_id={$parent_group_id['id']}");

        $permission_data = array(
            array(
                'permission' => 'index',
                'type' => 'a',
                'title' => 'Review Planned',
                'description' => 'Review inbound planned purchases',
                'position' => 4,
                'display' => 'true',
                'display_in_sidebar' => 'false',
                'parent_id' => $permission_parent_id['id'],
                'createdby' => 'admin',
                'module_id' => $module_id['id'],
                'component_id' => $module_component_id['id']
            )
        );

        $table = $this->table('permissions');
        $table->insert($permission_data);
        $table->save();

        file_put_contents('php://stderr', 'Permission added to review planned purchases, please update your user roles.' . PHP_EOL);
    }

    public function down() {
        return;
    }
}
