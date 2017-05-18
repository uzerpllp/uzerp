<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunityCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Opportunity', $tablename='opportunitiesoverview') {
		parent::__construct($do, $tablename);
		$this->identifierField='name';
			
		$this->view='';
	}
		
}
?>