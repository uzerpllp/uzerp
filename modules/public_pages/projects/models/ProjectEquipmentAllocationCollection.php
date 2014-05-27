<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectEquipmentAllocationCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.2 $';
	
	public $field;
		
	function __construct($do='ProjectEquipment', $tablename='project_equipment_allocation_overview') {

// Contruct the object
		parent::__construct($do, $tablename);
			
// Set specific characteristics
		$this->identifierField='name';
		$this->orderby=array('start_date', 'equipment');
	}

}
?>