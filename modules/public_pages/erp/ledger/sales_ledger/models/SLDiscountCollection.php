<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLDiscountCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	
	function __construct($do='SLDiscount', $tablename='sl_discounts_overview') {
		parent::__construct($do, $tablename);
	}

}
?>