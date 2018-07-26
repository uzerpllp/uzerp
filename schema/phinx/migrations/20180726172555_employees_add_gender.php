<?php

use UzerpPhinx\UzerpMigration;

class EmployeesAddGender extends UzerpMigration
{
    protected $cache_keys = array(
        '[table_fields][employees]',
    );

    /**
     * Add gender to employees table not null default 'O' => 'Other'
     */
    public function change()
    {
        $table = $this->table('employees');
        $table->addColumn('gender', 'text', array('null' => false, 'default' => 'O'))
              ->save();
        $this->CleanMemcache($this->cache_keys);
    }
}
