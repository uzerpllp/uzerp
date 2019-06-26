<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add new Manufacturing product search module componement
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddProductGroupSearchModuleComponent extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'manufacturing',
            'name' => 'productgroupsearch',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/manufacturing/models/productgroupsSearch.php'
        ]
    ];

    public function up()
    {
        $this->addModuleComponents();
    }

    public function down()
    {
        $this->removeModuleComponents();
    }
}
