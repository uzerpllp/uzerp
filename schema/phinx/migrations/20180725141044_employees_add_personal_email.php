<?php

use UzerpPhinx\UzerpMigration;

class EmployeesAddPersonalEmail extends UzerpMigration
{
    protected $cache_keys = array(
        '[table_fields][employees]',
    );

    /**
     * Add personal email id to employees table allow null values
     */
    public function change()
    {
        $table = $this->table('employees');
        $table->addColumn('contact_email_id', 'integer', array('null' => true,))
              ->save();
        $this->CleanMemcache($this->cache_keys);
    }
}
