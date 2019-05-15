<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add new Manufacturing module componements
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddMfJobModuleComponents extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'manufacturing',
            'name' => 'uzjobcostrollover',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/manufacturing/jobs/uzJobCostRollOver.php'
        ],
        [
            'module' => 'manufacturing',
            'name' => 'uzjobrecalclatestcosts',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/manufacturing/jobs/uzJobRecalcLatestCosts.php'
        ],
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
