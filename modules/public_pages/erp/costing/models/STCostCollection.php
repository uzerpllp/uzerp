<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STCostCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	
	public $field;
		
	public function __construct($do='STCost', $tablename='st_costsoverview') {
		parent::__construct($do, $tablename);
			
	}
		
}
?>