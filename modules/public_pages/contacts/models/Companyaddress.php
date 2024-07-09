<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class Companyaddress extends PartyAddress
{
    protected $version = '$Revision: 1.10 $';

    protected $defaultDisplayFields = [
        'name' => 'Name',
        'address' => 'Address',
        'main' => 'Main',
        'billing' => 'Billing',
        'shipping' => 'Shipping',
        'payment' => 'Payment',
        'technical' => 'Technical',
    ];

    public function __construct($tablename = 'companyaddress')
    {
        parent::__construct($tablename);

        $this->idField = 'id';

        $this->identifierField = 'address';
        $this->indestructable = [
            'main' => 't',
        ];

        $this->belongsTo('Country', 'countrycode', 'country');
        $this->belongsTo('Company', 'company_id', 'company');

        $this->setConcatenation('address', ['street1', 'street2', 'street3', 'town', 'county', 'postcode', 'country'], ',');
        $this->orderby = ['address'];
    }

    public function getAddresses($_company_id, $cc = '')
    {
        if (empty($cc)) {
            $cc = new ConstraintChain();
        }

        $cc->add(new Constraint('company_id', '=', $_company_id));

        return $this->getAll($cc, true, true);
    }

    public function getAll(ConstraintChain $cc = null, $ignore_tree = false, $use_collection = false, $limit = '')
    {
        return parent::getAll($cc, $ignore_tree, true, $limit);
    }
}

// End of Companyaddress
