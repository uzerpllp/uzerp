<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFWorkorderCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFWorkorder', $tablename='mf_workordersoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>