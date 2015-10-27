<?php
use UzerpPhinx\UzerpMigration;

class ProductDespatchSelect extends UzerpMigration
{
    protected $cache_keys = array(
        '[table_fields][so_product_lines_header]'
    );

    /**
     * Add field not_despatchable to so_product_lines_header
     *
     * This is a flag to indicate that this product's lines should
     * not be available to release for despatch
     */
    public function change()
    {
        $solines = $this->table('so_product_lines_header');
        $solines->addColumn('not_despatchable', 'boolean', array(
            'null' => true,
            'default' => false,
            'after' => 'description'
        ))->save();
        $this->CleanMemcache($this->cache_keys);
    }
}
