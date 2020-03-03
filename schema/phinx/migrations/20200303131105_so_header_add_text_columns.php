<?php


use UzerpPhinx\UzerpMigration;

class SoHeaderAddTextColumns extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('so_header');
        $table->addColumn('text1', 'char', ['null' => true, 'length' => 50])
              ->addColumn('text2', 'char', ['null' => true, 'length' => 50])
              ->addColumn('text3', 'char', ['null' => true, 'length' => 50])
              ->save();
    }
}
