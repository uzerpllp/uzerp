<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SalesPersonCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='SalesPerson', $tablename='sales_people_overview') {
		parent::__construct($do, $tablename);
		
	}
		
}
?>
