<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add VAT module componements
 * for VAT adjustments
 *
 * @author uzERP LLP and Martyn Shiner <mshiner@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2021 uzERP LLP (support#uzerp.com). All rights reserved.
 */

class AddVatAdjustmentModuleComponent extends UzerpMigration
{
    protected $module_components = [
        [
             'module' => 'vat',
            'name' => 'vatadjustment',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/ledger/vat/models/VatAdjustment.php'
        ],
        [
             'module' => 'vat',
            'name' => 'vatadjustmentcollection',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/ledger/vat/models/VatAdjustmentCollection.php'
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
