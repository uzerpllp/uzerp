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

class SoCostsAddComponentSocostssearch extends UzerpMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {

        // Add module_component row
        $module = $this->fetchRow("SELECT id FROM modules WHERE name = 'costing'");
        $module_component_data = array(
            array(
                'name' => 'socostssearch',
                'type' => 'M',
                'controller' => 'moduleobjects',
                'location' => 'modules/public_pages/erp/costing/models/socostsSearch.php',
                'module_id' => $module['id'],
                'createdby' => 'admin',
                'title' => 'Sales Order Product Header Costs Search Handler'
            )
        );
        $table = $this->table('module_components');
        $table->insert($module_component_data);
        $table->save();

    }
}
