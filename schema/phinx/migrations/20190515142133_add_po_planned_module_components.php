<?php


use UzerpPhinx\UzerpMigration;

class AddPoPlannedModuleComponents extends UzerpMigration
{
    protected $module_components = [
        [
            'module' => 'purchase_order',
            'name' => 'poplannedcontroller',
            'type' => 'C',
            'location' => 'modules/public_pages/erp/order/purchase_order/controllers/PoplannedController.php'
        ],
        [
            'module' => 'purchase_order',
            'name' => 'poplanned',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/order/purchase_order/models/POPlanned.php'
        ],
        [
            'module' => 'purchase_order',
            'name' => 'poplannedcollection',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/order/purchase_order/models/POPlannedCollection.php'
        ],
        [
            'module' => 'purchase_order',
            'name' => 'poplannedsearch',
            'type' => 'M',
            'location' => 'modules/public_pages/erp/order/purchase_order/models/poplannedSearch.php'
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
