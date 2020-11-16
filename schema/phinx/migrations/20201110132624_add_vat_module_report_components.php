<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add VAT module report componements
 * for PVA and Reverse Charge
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddVatModuleReportComponents extends UzerpMigration
{
    protected $module_components = [
        [
             'module' => 'vat',
            'name' => 'vatpvpurchases',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/ledger/vat/models/VatPVPurchases.php'
        ],
        [
             'module' => 'vat',
            'name' => 'vatpvpurchasescollection',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/ledger/vat/models/VatPVPurchasesCollection.php'
        ],
        [
            'module' => 'vat',
           'name' => 'vatrcpurchases',
           'type' => 'M',
           'location' => 'modules/public_pages/erp/ledger/vat/models/VatRCPurchases.php'
       ],
       [
            'module' => 'vat',
           'name' => 'vatrcpurchasescollection',
           'type' => 'M',
           'location' => 'modules/public_pages/erp/ledger/vat/models/VatRCPurchasesCollection.php'
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
