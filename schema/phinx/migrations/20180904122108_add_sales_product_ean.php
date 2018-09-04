<?php


use UzerpPhinx\UzerpMigration;

class AddSalesProductEan extends UzerpMigration
{
    /**
     * Add EAN field to productline header.
     */
    public function change()
    {
        $table = $this->table('so_product_lines_header');
        $table->addColumn('ean', 'string', ['limit' => 13, 'null' => true])
              ->save();
    }
}
