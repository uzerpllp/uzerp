<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PersonattachmentsController extends AttachmentsController
{
    protected $version = '$Revision: 1.6 $';

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        // Set up some variables
        $this->setModule('contacts');

        $this->setController('personattachments');

        $this->setModel('person');

        $this->setIdField('person_id');
    }
}

// End of PersonattachmentsController
