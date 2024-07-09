<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class Personaddress extends DataObject
{
    protected $version = '$Revision: 1.8 $';

    protected $defaultDisplayFields = [
        'name' => 'Name',
        'address' => 'Address',
        'main' => 'Main',
        'billing' => 'Billing',
        'shipping' => 'Shipping',
        'payment' => 'Payment',
        'technical' => 'Technical',
    ];

    public function __construct($tablename = 'personaddress')
    {
        parent::__construct($tablename);

        $this->idField = 'id';
        $this->identifierField = 'address';

        $this->belongsTo('Country', 'countrycode', 'country');
        $this->belongsTo('Person', 'person_id', 'person');
        $this->setConcatenation('address', ['street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country'], ',');
        $this->orderby = ['address'];
    }

    public function getAddresses($_person_id, $cc = '')
    {
        if (empty($cc)) {
            $cc = new ConstraintChain();
        }

        $cc->add(new Constraint('person_id', '=', $_person_id));

        return $this->getAll($cc, true, true);
    }
}

// End of Personaddress
