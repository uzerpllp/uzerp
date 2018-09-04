<?php


use UzerpPhinx\UzerpMigration;

class RemoveEan extends UzerpMigration
{
    /**
     * Remove EAN field from productlines
     */
    public function change()
    {
        $table = $this->table('so_product_lines');
        $table->removeColumn('ean')
              ->save();
    }
}
