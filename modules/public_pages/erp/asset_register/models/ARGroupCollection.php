<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ARGroupCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.6 $';
	
	public $field;
	
	function __construct($do = 'ARGroup', $tablename = 'ar_groupsoverview')
	{
		
		parent::__construct($do, $tablename);
		
	}
	
}

// End of ARGroupCollection
