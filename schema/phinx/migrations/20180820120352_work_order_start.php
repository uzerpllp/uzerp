<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add start date to work orders to support Freppl Integration
 */
class WorkOrderStart extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('mf_workorders');
        $table->addColumn('start_date', 'date', ['null' => true])
              ->save();
    }
}
