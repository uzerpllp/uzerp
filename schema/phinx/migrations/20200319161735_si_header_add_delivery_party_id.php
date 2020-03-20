<?php


use UzerpPhinx\UzerpMigration;

class SiHeaderAddDeliveryPartyId extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('si_header');
        $table->addColumn('del_partyaddress_id', 'integer', ['null' => true])
              ->save();
    }
}
