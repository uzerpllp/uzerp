<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class LeadCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Lead', $tablename='companyoverview') {
		parent::__construct($do, $tablename);

		$this->identifier='name';
		$this->identifierField='name';
	}
	
}
?>