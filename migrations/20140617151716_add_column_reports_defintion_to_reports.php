<?php

use Phinx\Migration\AbstractMigration;

class AddColumnReportsDefintionToReports extends AbstractMigration
{
    /**
     * Add report_definition field to reports table and allow null values
     */
    public function change()
    {
        $reports = $this->table('reports');
        $reports->addColumn('report_definition', 'integer', array('null' => true,))
                ->addForeignKey('report_definition', 'report_definitions', 'id')
                ->save();
    }
}
