<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SLCustomerCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.11 $';
	
	public $field;

	public $agedBalances=array();
	
	function __construct($do='SLCustomer', $tablename='slmaster_overview')
	{
		parent::__construct($do, $tablename);
		
	}

}

// End of SLCustomerCollection
