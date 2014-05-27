<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectEquipmentAllocation extends DataObject {
	
	protected $version='$Revision: 1.4 $';
	
	public function __construct($tablename='project_equipment_allocation') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->orderby=array('start_date');
		
// Define relationships
		$this->belongsTo('Project', 'project_id', 'project');
		$this->belongsTo('Task', 'task_id', 'task');
		$this->belongsTo('ProjectEquipment', 'project_equipment_id', 'equipment');
		$this->belongsTo('STuom', 'charge_rate_uom_id', 'charge_rate_uom');
		$this->belongsTo('STuom', 'charging_period_uom_id', 'charging_period_uom');
		
// Define field formats
		$this->getField('charge_rate')->setFormatter(new PriceFormatter());
		$this->getField('setup_charge')->setFormatter(new PriceFormatter());
		
// Define field defaults
		
// Define validation
		
// Define enumerated types
		
// Define Access Rules

// Define link rules for sidebar related view
	
	}

	public function getChargeTotals ($_project_id='', $_task_id='') {
		
		if (empty($_project_id)) { return array(); }
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('project_id', '=', $_project_id));
		
		if (!empty($_task_id))
		{
			$cc->add(new Constraint('task_id', '=', $_task_id));
		}
		
		$totals=$this->getSumFields(
				array(
						'total_costs',
						'total_charges'
					),
					$cc
					,'project_equipment_charges'
				);

		return $totals;
		
	}
	
	public function getNetValue () {
		
	}

}
?>