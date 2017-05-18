<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFStructureCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFStructure', $tablename='mf_structuresoverview') {
		parent::__construct($do, $tablename);
		$this->title='Item Structure';
	}

}
?>