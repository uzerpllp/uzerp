<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFOperationCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='MFOperation', $tablename='mf_operationsoverview') {
		parent::__construct($do, $tablename);
		$this->title='Manufacturing Operations';
	}

}
?>