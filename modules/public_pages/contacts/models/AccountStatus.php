<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AccountStatus extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	function __construct($tablename = 'account_statuses')
	{
		parent::__construct($tablename);
	}

}

// End of AccountStatus
