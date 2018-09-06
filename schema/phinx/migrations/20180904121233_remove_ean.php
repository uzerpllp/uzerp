<?php


use UzerpPhinx\UzerpMigration;

class RemoveEan extends UzerpMigration
{
    /**
     * Remove EAN from sales productline
     */
    public function up()
    {
        $table = $this->table('so_product_lines');
        $table->removeColumn('ean')
              ->save();
    }

    /**
     * Add EAN to sales productline
     */
    public function down()
    {
        $table = $this->table('so_product_lines');
        $table->addColumn('ean', 'text', ['null' => true])
              ->save();
    }
}
