<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFOutsideOperationCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFOutsideOperation', $tablename='mf_outside_opsoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>