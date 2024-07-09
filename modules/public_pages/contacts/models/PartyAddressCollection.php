<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PartyAddressCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.6 $';

    public function __construct($do = 'PartyAddress', $tablename = 'partyaddressoverview')
    {
        parent::__construct($do, $tablename);

        $this->identifier = 'name';
        $this->identifierField = 'name';
    }
}

// End of PartyAddressCollection
