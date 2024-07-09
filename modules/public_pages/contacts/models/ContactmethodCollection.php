<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class ContactmethodCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.5 $';

    public $field;

    public function __construct($do = 'Contactmethod')
    {
        parent::__construct($do);
    }
}

// End of ContactmethodCollection
