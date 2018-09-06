<?php


use Phinx\Migration\AbstractMigration;

class StitemCostBasis extends AbstractMigration
{

    /*
     * Add cost_basis column to st_items table
     */
    public function change()
    {
        $stitems = $this->table('st_items')
                        ->addColumn('cost_basis', 'string', ['after' => 'text1', 'default' => 'VOLUME'])
                        ->save();
    }
}
