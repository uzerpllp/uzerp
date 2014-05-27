<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenseCollection extends DataObjectCollection
{
		
	protected $version = '$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do = 'Expense', $tablename = 'expenses_header_overview')
	{
		parent::__construct($do, $tablename);

	}

}

// End of ExpenseCollection
