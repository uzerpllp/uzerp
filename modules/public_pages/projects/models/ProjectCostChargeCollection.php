<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectCostChargeCollection extends DataObjectCollection {
	
	protected $identifierField;
	
	public $field;
		
	function __construct($do='ProjectCostCharge') {
		parent::__construct($do);
			
		$this->identifierField='item_type';
	}

}
?>