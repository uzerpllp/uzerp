<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyIndustry extends DataObject
{
    protected $version = '$Revision: 1.5 $';

    public function __construct($tablename = 'company_industries')
    {
        parent::__construct($tablename);
    }
}

// End of CompanyIndustry
