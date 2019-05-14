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

class PermissionSubmitVat extends UzerpMigration
{
    /**
     * Add permission to enable VAT return submission via HMRC API (MTD)
     */
    public function up() {
        $module_id = $this->fetchRow("SELECT id FROM modules WHERE name = 'vat'");
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'vatcontroller'");
        $permission_parent_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'vat' AND type = 'c'");

        $permission_data = array(
            array(
                'permission' => 'hmrcpostvat',
                'type' => 'a',
                'title' => 'Submit VAT Return',
                'description' => 'Submit VAT using HMRC API',
                'position' => 5,
                'display' => false,
                'display_in_sidebar' => false,
                'parent_id' => $permission_parent_id['id'],
                'createdby' => 'admin',
                'module_id' => $module_id['id'],
                'component_id' => $module_component_id['id']
            )
        );

        $table = $this->table('permissions');
        $table->insert($permission_data);
        $table->save();

        file_put_contents('php://stderr', 'Permission added to submit VAT Return (MTD), please update your user roles.' . PHP_EOL);
    }

    public function down() {
        return;
    }
}
