<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class Note extends DataObject
{
    protected $version = '$Revision: 1.5 $';

    protected $defaultDisplayFields = ['title', 'note', 'created'];

    public function __construct($tablename = 'person_notes')
    {
        parent::__construct($tablename);

        $this->belongsTo('Person', 'person_id', 'person');
    }
}

// End of Note
