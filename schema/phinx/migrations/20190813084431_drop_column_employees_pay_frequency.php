<?php


use UzerpPhinx\UzerpMigration;

class DropColumnEmployeesPayFrequency extends UzerpMigration
{
    /**
     * Change Method.
     * 
     * Remove Pay Frequency column which is deprecated
     * 
     * Drop pay_frequency_id 
     *
     */
    public function change()
    {
        $table = $this->table('employees');        
        $table->removeColumn('pay_frequency_id')
              ->save();
    }
}
