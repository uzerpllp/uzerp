<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyCollection extends PartyCollection
{
	
	protected $version='$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do = 'Company', $tablename = 'companyoverview')
	{
		parent::__construct($do, $tablename);
			
		$this->identifier		= 'name';
		$this->identifierField	= 'name';
	}

}

// End of CompanyCollection
