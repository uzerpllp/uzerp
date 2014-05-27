<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LedgerCategoryCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.4 $';
	
	public $field;
	
	function __construct($do = 'LedgerCategory', $tablename = 'ledger_categories_overview')
	{
		parent::__construct($do, $tablename);
			
	}
	
}

// End of LedgerCategoryCollection
