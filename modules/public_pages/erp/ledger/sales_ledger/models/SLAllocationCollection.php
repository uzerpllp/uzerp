<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLAllocationCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	public $field;
	
	function __construct($do='SLAllocation', $tablename='sl_allocation_details_overview') {
		parent::__construct($do, $tablename);
		
	}
	
}
?>