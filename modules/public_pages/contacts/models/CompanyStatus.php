<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyStatus extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($tablename = 'company_statuses')
	{
		parent::__construct($tablename);
	}

}

// End of CompanyStatus
