<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class PartyNoteCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.5 $';

    public function __construct($do = 'PartyNote', $tablename = 'party_notesoverview')
    {
        parent::__construct($do, $tablename);
    }
}

// End of PartyNoteCollection
