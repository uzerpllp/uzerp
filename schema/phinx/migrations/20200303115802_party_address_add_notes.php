<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add notes field to partyaddress
 */
class PartyAddressAddNotes extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('partyaddress');
        $table->addColumn('notes', 'text', ['null' => true])
              ->save();
    }
}