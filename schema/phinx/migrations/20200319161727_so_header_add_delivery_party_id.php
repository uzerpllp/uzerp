<?php


use UzerpPhinx\UzerpMigration;

class SoHeaderAddDeliveryPartyId extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('so_header');
        $table->addColumn('del_partyaddress_id', 'integer', ['null' => true])
              ->save();
    }
}
