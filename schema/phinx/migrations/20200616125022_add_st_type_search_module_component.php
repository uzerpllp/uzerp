<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add new Stock Type Code search module component
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddStTypeSearchModuleComponent extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'manufacturing',
            'name' => 'sttypecodesearch',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/manufacturing/models/STTypecodeSearch.php'
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
