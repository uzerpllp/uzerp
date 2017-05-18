<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenseLineCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do = 'ExpenseLine', $tablename = 'expenses_lines_overview')
	{
		parent::__construct($do, $tablename);

	}

}

// End of ExpenseLineCollection
