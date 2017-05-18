<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaxStatusCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	public $field;
		
	function __construct($do='TaxStatus') {
		parent::__construct($do);
//		$this->_tablename="tax_statusesoverview";
			
	}

}
?>