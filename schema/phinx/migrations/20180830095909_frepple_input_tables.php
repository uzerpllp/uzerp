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
        $po_planned->addColumn('item_code', 'string', ['null' => true])
                   ->addColumn('quantity', 'integer', ['null' => true])
                   ->addColumn('stitem_id', 'biginteger', ['null' => true])
                   ->addColumn('plmaster_id', 'biginteger', ['null' => true])
                   ->addColumn('startdate', 'datetime', ['null' => true])
                   ->addColumn('enddate', 'datetime', ['null' => true])
                   ->save();

        $mf_planned = $this->table('mf_planned');
        $mf_planned->addColumn('item_code', 'string', ['null' => true])
                   ->addColumn('quantity', 'integer', ['null' => true])
                   ->addColumn('stitem_id', 'biginteger', ['null' => true])
                   ->addColumn('startdate', 'datetime', ['null' => true])
                   ->addColumn('enddate', 'datetime', ['null' => true])
                   ->save();

        $this->query("ALTER TABLE po_planned OWNER TO \"{$this->owner}\"");
        $this->query("ALTER TABLE mf_planned OWNER TO \"{$this->owner}\"");
    }
}
