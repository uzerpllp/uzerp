<?php

use UzerpPhinx\UzerpMigration;

class ReportingAddGroup extends UzerpMigration
{
    /**
     * Add group column to reports table
     */
    public function change()
    {
        $table = $this->table('reports');
        $table->addColumn('report_group', 'text', array('null' => true,))
              ->save();

    }
}
