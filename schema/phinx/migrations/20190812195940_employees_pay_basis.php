<?php


use UzerpPhinx\UzerpMigration;

class EmployeesPayBasis extends UzerpMigration
{
    /**
     * Add an enumerated Pay Basis column, Not Null
     * Values are M or W - defaulted to M
     */

    public function change()
    {
        
        $table = $this->table('employees');
        $table->addColumn('pay_basis', 'text', array('after' => 'employee_grade_id','null' => false, 'default' => 'M'))
              ->save();

        $this->CleanMemcache($this->cache_keys);

    }
}
