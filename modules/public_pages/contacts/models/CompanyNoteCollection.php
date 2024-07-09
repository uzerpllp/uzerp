<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyNoteCollection extends DataObjectCollection
{
    protected $version = '$Revision: 1.5 $';

    public function __construct($do = 'CompanyNote', $tablename = 'company_notesoverview')
    {
        parent::__construct($do, $tablename);
    }
}

// End of CompanyNoteCollection
