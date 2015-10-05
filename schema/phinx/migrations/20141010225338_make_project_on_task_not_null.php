<?php

use Phinx\Migration\AbstractMigration;

class MakeProjectOnTaskNotNull extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
     */
    public function change()
    {
        $rowcount = $this->execute('ALTER TABLE tasks ALTER COLUMN project_id SET NOT NULL');
    }
    
   
}