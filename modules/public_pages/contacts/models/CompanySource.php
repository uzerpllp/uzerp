<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanySource extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($tablename = 'company_sources')
	{
		parent::__construct($tablename);
	}

}

// End of CompanySource
