<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add VAT number field to partyaddress
 * 
 * Supports retrieval of VAT number of landed entity
 * in invoices and reporting.
 * 
 * VAT on goods depends on the country the goods are
 * landed. A VAT number on an invoice shipping address
 * would be used in preference to the invoiced company's
 * VAT number, which is held on the sl_master record.
 */
class PartyAddressAddVatNumber extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('partyaddress');
        $table->addColumn('vatnumber', 'string', ['null' => true])
              ->save();
    }
}
