<?php


use UzerpPhinx\UzerpMigration;

class RemovePurchaseEan extends UzerpMigration
{
    /**
     * Remove EAN from purchase productline
     */
    public function up()
    {
        $table = $this->table('po_product_lines');
        $table->removeColumn('ean')
              ->save();
    }

    /**
     * Add EAN to purchase productline
     */
    public function down()
    {
        $table = $this->table('po_product_lines');
        $table->addColumn('ean', 'text', ['null' => true])
              ->save();
    }
}
