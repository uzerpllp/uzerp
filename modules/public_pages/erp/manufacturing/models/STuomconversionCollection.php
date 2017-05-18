<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STuomconversionCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='STuomconversion', $tablename='st_uomconversionsoverview') {
		parent::__construct($do, $tablename);
		$this->title='UoM Conversions';
	}

}
?>