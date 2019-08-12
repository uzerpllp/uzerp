<?php


use UzerpPhinx\UzerpMigration;

class AddMfCentreAvailableQty extends UzerpMigration
{
    /*
     * Add available_qty column to mf_centres
     */
    public function change()
    {
        $stitems = $this->table('mf_centres')
                        ->addColumn('available_qty', 'integer', ['default' => 1])
                        ->save();
    }
}
