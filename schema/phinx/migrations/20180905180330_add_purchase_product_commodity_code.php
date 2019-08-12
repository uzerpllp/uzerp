<?php


use UzerpPhinx\UzerpMigration;

class AddPurchaseProductCommodityCode extends UzerpMigration
{
    /**
     * Add commodity field to productline header
     */
    public function change()
    {
        $table = $this->table('po_product_lines_header');
        $table->addColumn('commodity_code', 'string', ['null' => true])
              ->save();
    }
}
