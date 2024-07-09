<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PartyAddress extends DataObject
{
    protected $version = '$Revision: 1.20 $';

    protected $defaultDisplayFields = [
        'name' => 'Name',
        'vatnumber' => 'VAT Number',
        'fulladdress' => 'Address',
        'main' => 'Main',
        'billing' => 'Billing',
        'shipping' => 'Shipping',
        'payment' => 'Payment',
        'technical' => 'Technical',
    ];

    public function __construct($tablename = 'partyaddress')
    {
        parent::__construct($tablename);

        $this->idField = 'id';

        $this->hasOne('Address', 'address_id', 'address');
        $this->hasOne('Party', 'party_id', 'party');

        $this->belongsTo('Address', 'address_id', 'main_address');

        $this->setComposite('Address', 'address_id', 'fulladdress', ['street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country']);

        $this->getField('name')->setDefault('MAIN');
    }

    public function getAddress()
    {
        return $this->fulladdress;
    }

    public function delete($id = null, &$errors = [], $archive = false, $archive_table = null, $archive_schema = null)
    {
        if (empty($id)) {
            $errors[] = 'No Party Address identifier';
            return false;
        }

        $this->load($id);

        if (! $this->isLoaded()) {
            $errors[] = 'Cannot find Party Address record';
            return false;
        }

        $db = DB::Instance();
        $flash = Flash::Instance();

        $db->StartTrans();

        if (! parent::delete()) {
            $db->FailTrans();
            $flash->addError($db->ErrorMsg());
        }

        // Delete the address if it is not used elsewhere
        if (! $this->address->delete($this->address->id, $errors)) {
            $db->FailTrans();
            $flash->addError($db->ErrorMsg());
        }

        return $db->CompleteTrans();
    }

    public function save($debug = false)
    {
        $partyaddress = DataObjectFactory::Factory('PartyAddress');

        $partyaddress->load($this->id);

        if ($partyaddress->isLoaded() && $this->address_id != $partyaddress->address_id) {
            // Update to Party Address, now points to different address
            // so get the old address id for use below
            $old_address = $partyaddress->address_id;
        }

        $db = DB::Instance();
        $db->StartTrans();

        $errors = [];

        if ($this->main[0] == 't') {
            // This will be a 'main' address and must not have a VAT number set.
            // Make sure it is empty
            $this->vatnumber = '';

            $cc = new ConstraintChain();

            $cc->add(new Constraint('party_id', '=', $this->party_id));
            $cc->add(new Constraint('main', 'is', 'true'));

            foreach ($partyaddress->getAll($cc) as $id => $value) {
                if ($partyaddress->id != $id && ! $partyaddress->update($id, 'main', 'false')) {
                    $errors[] = 'Error updating addresses : ' . $db->ErrorMsg();
                }
            }
        }

        $result = parent::save();

        if ($result && ! empty($old_address)) {
            // Party address has been saved OK, and address has changed
            // so delete old address if no other party is linked to it
            $address = DataObjectFactory::Factory('Address');
            $result = $address->delete($old_address, $errors);
        }

        if ($result === false || count($errors) > 0) {
            $flash = Flash::Instance();
            $flash->addErrors($errors);
            $db->FailTrans();
        }

        $db->CompleteTrans();

        return $result;
    }

    public function checkAddress(&$data)
    {
        $address = DataObjectFactory::Factory('Address');
        // If the address id field is present, this is an update
        // - if there is no change to address, do nothing
        // - if no one else has this address, do nothing
        // - otherwise, treat as an insert
        if (empty($data['Address'][$address->idField])) {
            // Use existing address if match found
            $address->check_exists($data['Address']);

            if ($address->isLoaded()) {
                foreach ($data['Address'] as $fieldname => $value) {
                    if ($address->isField($fieldname)) {
                        $data['Address'][$fieldname] = $address->$fieldname;
                    }
                }
                $data['PartyAddress']['address_id'] = $address->{$address->idField};
            }

            return;
        }

        $address->load($data['Address'][$address->idField]);

        $count = $address->parties->count();

        // If the address exists then there should be a party for it!
        if ($count == 0) {
            return;
        }

        // if there is only one partyaddress for the address
        // and we are not inserting a partyaddress then
        // this is an update to the existing single partyaddress
        if ($count == 1 && ! empty($data['PartyAddress'][$this->idField])) {
            return;
        }

        // If this is a change to the existing address used by more than one party
        // so make it an address insert
        foreach ($address->getFields() as $fieldname => $field) {
            if (isset($data['Address'][$fieldname]) && $data['Address'][$fieldname] != $field->value) {
                $data['Address'][$address->idField] = '';
                $data['PartyAddress']['address_id'] = '';

                return;
            }
        }
    }
}

// End of PartyAddress
