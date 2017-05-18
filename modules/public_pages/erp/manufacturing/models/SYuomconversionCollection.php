<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SYuomconversionCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='SYuomconversion', $tablename='sy_uomconversionsoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>