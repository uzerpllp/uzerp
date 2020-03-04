<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add fk to report definition on slmaster
 * 
 * Supports per customer output, e.g. invoice layout
 */
class SlCustomerAddReportDefFk extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('slmaster');
        $table->addColumn('report_def_id', 'integer', ['null' => true])
              ->save();
    }
}
