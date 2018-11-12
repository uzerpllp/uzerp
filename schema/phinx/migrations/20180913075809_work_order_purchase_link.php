<?php

use UzerpPhinx\UzerpMigration;

/**
 * Enable po_lines to be linked to mf_operation(s)
 */
class WorkOrderPurchaseLink extends UzerpMigration
{

    public function up()
    {
        $table = $this->table('po_lines')
        ->addColumn('mf_workorders_id', 'biginteger', ['null'=> true])
        ->addColumn('mf_operations_id', 'biginteger', ['null' => true])
        ->save();
    }

    public function down() {
        $table = $this->table('po_lines')
        ->removeColumn('mf_workorders_id')
        ->removeColumn('mf_operations_id')
        ->save();
    }
}
