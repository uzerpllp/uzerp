<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class Personcontactmethod extends DataObject
{
    protected $version = '$Revision: 1.5 $';

    protected $defaultDisplayFields = [
        'name' => 'Name',
        'contact' => 'Contact',
        'main' => 'Main',
        'billing' => 'Billing',
        'shipping' => 'Shipping',
        'payment' => ' Payment',
        'technical' => 'Technical',
    ];

    public function __construct($tablename = 'person_contact_methods')
    {
        parent::__construct($tablename);

        $this->idField = 'id';
        $this->identifierField = 'name';

        $this->belongsTo('Person', 'person_id', 'person');
    }

    public function __toString()
    {
        $value = $this->_fields['contact']->value;

        if (empty($value)) {
            $value = '';
        }

        return $value;
    }
}

// End of Personcontactmethod
