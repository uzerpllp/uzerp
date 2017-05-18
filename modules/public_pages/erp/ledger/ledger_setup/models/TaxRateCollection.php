<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaxRateCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	public $field;
		
	function __construct($do='TaxRate', $tablename='taxrates') {
		parent::__construct($do, $tablename);
			
	}

}
?>