<?php


use UzerpPhinx\UzerpMigration;

class FreppleInputTables extends UzerpMigration
{
    protected $owner = 'www-data';

    public function change()
    {
        $po_planned = $this->table('po_planned');
        $po_planned->addColumn('item_code', 'string')
                   ->addColumn('quantity', 'integer')
                   ->addColumn('stitem_id', 'biginteger')
                   ->addColumn('plmaster_id', 'biginteger')
                   ->addColumn('startdate', 'datetime')
                   ->addColumn('enddate', 'datetime')
                   ->save();

        $mf_planned = $this->table('mf_planned');
        $mf_planned->addColumn('item_code', 'string')
                   ->addColumn('quantity', 'integer')
                   ->addColumn('stitem_id', 'biginteger')
                   ->addColumn('startdate', 'datetime')
                   ->addColumn('enddate', 'datetime')
                   ->save();

        $this->query("ALTER TABLE po_planned OWNER TO \"{$this->owner}\"");
        $this->query("ALTER TABLE mf_planned OWNER TO \"{$this->owner}\"");
    }

    public function up()
    {
        $view_name = 'mf_plannedoverview';
        $view = <<<VIEW
 SELECT sti.id AS stitem_id,
    sti.item_code,
    sti.description,
    mfp.quantity AS qty,
    sti.uom_id,
    u.uom_name,
    mfp.startdate AS start_date,
    mfp.enddate AS required_by
   FROM mf_planned mfp
     JOIN st_items sti ON sti.id = mfp.stitem_id
     JOIN st_uoms u ON u.id = sti.uom_id;
VIEW;

        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$this->owner}\"");
    }
}
