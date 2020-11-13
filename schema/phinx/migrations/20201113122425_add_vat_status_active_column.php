<?php


use UzerpPhinx\UzerpMigration;

class AddVatStatusActiveColumn extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('tax_statuses');
        $table->addColumn('active', 'boolean', ['default' => true])
              ->save();
    }
}
