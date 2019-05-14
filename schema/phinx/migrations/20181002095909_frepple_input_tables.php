<?php


use UzerpPhinx\UzerpMigration;

/**
 * Database tables to hold planned orders
 */
class FreppleInputTables extends UzerpMigration
{
    protected $owner = 'www-data';

    public function change()
    {
        $po_planned = $this->table('po_planned');
        $po_planned->addColumn('item_code', 'string', ['null' => false])
                   ->addColumn('supplier_name', 'string', ['null' => false])
                   ->addColumn('order_date', 'date', ['null' => false])
                   ->addColumn('delivery_date', 'date', ['null' => false])
                   ->addColumn('qty', 'decimal', ['null' => false])
                   ->addColumn('product_group_desc', 'string', ['null' => true])
                   ->addColumn('description', 'string', ['null' => true])
                   ->save();
        $this->query("ALTER TABLE po_planned OWNER TO \"{$this->owner}\"");
    }
}
