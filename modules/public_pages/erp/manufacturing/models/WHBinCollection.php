<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHBinCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='WHBin', $tablename='wh_binsoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>