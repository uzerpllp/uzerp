<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFWOStructureCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFWOStructure', $tablename='mf_wostructuresoverview') {
		parent::__construct($do, $tablename);
		$this->title='Works Order Structures';
	}
		
}
?>