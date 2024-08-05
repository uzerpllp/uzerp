<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectEquipment extends DataObject {
	
	protected $version='$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'setup_cost'
										   ,'cost_rate'
										   ,'uom_name'
										   ,'available');
										   
	public function __construct($tablename='project_equipment') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);


// Set specific characteristics

// Define relationships
		$this->belongsTo('STuom', 'uom_id', 'uom_name');
		$this->hasMany('ProjectEquipmentAllocation', 'allocations', 'equipment_id');

// Define field formats
		$this->getField('cost_rate')->setFormatter(new PriceFormatter());
		$this->getField('setup_cost')->setFormatter(new PriceFormatter());

// Define field defaults

// Define validation

// Define enumerated types

// Define Access Rules

// Define link rules for sidebar related view
	
	}

}
?>