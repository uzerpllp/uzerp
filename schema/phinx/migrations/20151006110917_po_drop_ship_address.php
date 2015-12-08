<?php
use Phinx\Migration\AbstractMigration;

class PoDropShipAddress extends UzerpPhinx\UzerpMigration
{

    protected $cache_keys = array(
        '[table_fields][po_header]'
    );

    /**
     * Add field use_sorder_delivery to po_header
     *
     * This is a flag to indicate that the user would like the delivery address
     * to be sourced from the linked sales order, instead of the purchase order.
     */
    public function change()
    {
        $solines = $this->table('po_header');
        $solines->addColumn('use_sorder_delivery', 'boolean', array(
            'null' => true
        ))->save();
        $this->CleanMemcache($this->cache_keys);
    }
}
