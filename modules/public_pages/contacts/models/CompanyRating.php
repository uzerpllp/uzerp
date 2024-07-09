<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyRating extends DataObject
{
    protected $version = '$Revision: 1.5 $';

    public function __construct($tablename = 'company_ratings')
    {
        parent::__construct($tablename);
    }
}

// End of CompanyRating
