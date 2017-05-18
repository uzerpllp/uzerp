<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenseAuthoriserCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do = 'ExpenseAuthoriser', $tablename = 'expense_authorisers_overview')
	{
		
		parent::__construct($do, $tablename);

	}

}

// End of ExpenseAuthoriserCollection
