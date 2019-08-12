<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add new VAT module componements
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddVatModuleComponents extends UzerpMigration
{
    protected $module_components = [
            [
                'module' => 'vat',
                'name' => 'vatinputs',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatInputs.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vatinputscollection',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatInputsCollection.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vatoutputs',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatOutputs.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vatoutputscollection',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatOutputsCollection.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vateupurchases',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuPurchases.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vateupurchasescollection',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuPurchasesCollection.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vateusales',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuSales.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vateusalescollection',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatEuSalesCollection.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vattranssearch',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatTransSearch.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vatreturn',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatReturn.php'
            ],
            [
                'module' => 'vat',
                'name' => 'vatreturncollection',
                'type' => 'M',
                'location' => 'modules/public_pages/erp/ledger/vat/models/VatReturnCollection.php'
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
