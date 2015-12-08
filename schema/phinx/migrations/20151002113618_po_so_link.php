<?php
use UzerpPhinx\UzerpMigration;

class PoSoLink extends UzerpMigration
{
    protected $cache_keys = array(
        '[table_fields][po_header]',
    );
    
    /**
     * Add so_header_id to po_header table to hold optional link to SO from PO and allow null values
     */
    public function change()
    {
        $solines = $this->table('po_header');
        $solines->addColumn('sales_order_id', 'integer', array(
            'null' => true
        ))->save();
        $this->CleanMemcache($this->cache_keys);
        
    }
}