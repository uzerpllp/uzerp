<?php

use UzerpPhinx\UzerpMigration;

class CompanyAddTextFields extends UzerpMigration
{
    /**
     * Add text fields to company table
     */
    public function change()
    {
        $table = $this->table('company');
        $table->addColumn('text1', 'text', array('null' => true,))
              ->addColumn('text2', 'text', array('null' => true,))
              ->save();
    }
}
