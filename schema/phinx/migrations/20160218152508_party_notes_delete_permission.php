<?php

use Phinx\Migration\AbstractMigration;

/**
 * Phinx Migration
 *
 * Add delete permission for Party Notes
 *
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2015 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class PartyNotesDeletePermission extends AbstractMigration
{

    public function up()
    {
        // Add permission
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'contacts'");
        $module_component_id = $this->fetchRow("SELECT id FROM module_components WHERE name = 'partynotescontroller' AND module_id = ${module['id']}");
        $permission_parent_id = $this->fetchRow("SELECT id FROM permissions WHERE permission = 'partynotes' AND title = 'Party Notes' AND module_id = ${module['id']}");

        $permission_data = array(
            array(
                'permission' => 'delete',
                'type' => 'a',
                'title' => 'Delete Party Note',
                'position' => 4,
                'display' => 'false',
                'display_in_sidebar' => 'false',
                'parent_id' => $permission_parent_id['id'],
                'createdby' => 'admin',
                'module_id' => $module['id'],
                'component_id' => $module_component_id['id']
            )
        );

        $table = $this->table('permissions');
        $table->insert($permission_data);
        $table->save();

        file_put_contents('php://stderr', 'Permission added for delete in module PartynotesController, please update your user roles.' . PHP_EOL);
    }
}

