<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class AddressCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.6 $';

    public $field;

    public function __construct($do = 'Address', $tablename = 'addressoverview')
    {
        parent::__construct($do, $tablename);
    }
}

// End of AddressCollection
