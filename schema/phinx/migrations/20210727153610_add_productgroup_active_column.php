<?php


use UzerpPhinx\UzerpMigration;

class AddProductgroupActiveColumn extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('st_productgroups');
        $table->addColumn('active', 'boolean', ['default' => true])
              ->save();
    }
}
