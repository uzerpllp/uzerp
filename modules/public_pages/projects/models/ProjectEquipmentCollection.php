<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectEquipmentCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='ProjectEquipment', $tablename='project_equipment_overview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='name';
	}

}
?>