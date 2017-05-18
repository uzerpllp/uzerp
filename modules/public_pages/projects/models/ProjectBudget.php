<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectBudget extends DataObject {
	
	protected $version='$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('project'
										   ,'task'
										   ,'budget_item_type'
										   ,'description'
										   ,'quantity'
										   ,'uom_name'
										   ,'cost_rate'
										   ,'charge_rate'
										   ,'setup_cost'
										   ,'setup_charge'
										   ,'total_cost_rate'
										   ,'total_charge_rate'
										   );
	
	public function __construct($tablename='project_budgets', $item_type='') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics

// Define relationships
		$this->belongsTo('Project', 'project_id');
		$this->belongsTo('Task', 'task_id');
		$this->belongsTo('STuom', 'uom_id', 'uom_name');
		
// Define field formats
		$this->getField('setup_cost')->setFormatter(new PriceFormatter());
		$this->getField('cost_rate')->setFormatter(new PriceFormatter());
		$this->getField('total_cost_rate')->setFormatter(new PriceFormatter());
		$this->getField('setup_charge')->setFormatter(new PriceFormatter());
		$this->getField('charge_rate')->setFormatter(new PriceFormatter());
		$this->getField('total_charge_rate')->setFormatter(new PriceFormatter());
		
// The name for these links is determined from the enumerated type
		switch (strtoupper($item_type)) {
			case 'R' :
				$this->hasOne('SOProductline', 'budget_item_id', 'r');
				break;
			case 'E' :
				$this->hasOne('Equipment', 'budget_item_id', 'e');
				break;
			case 'M' :
				$this->hasOne('STItem', 'budget_item_id', 'm');
				break;
			case 'L' :
				$this->hasOne('MFResource', 'budget_item_id', 'l');
				break;
		}
		
// Define field defaults
		$this->getField('setup_cost')->setDefault('0.00');
		$this->getField('cost_rate')->setDefault('0.00');
		$this->getField('total_cost_rate')->setDefault('0.00');
		$this->getField('setup_charge')->setDefault('0.00');
		$this->getField('charge_rate')->setDefault('0.00');
		$this->getField('total_charge_rate')->setDefault('0.00');
		
// Define validation
		
// Define enumerated types
 		$this->setEnum('budget_item_type'
							,array('R'=>'Revenue'
								,'M'=>'Materials'
								,'L'=>'Labour'
								,'E'=>'Equipment'
								,'O'=>'Other'
								  )
						);
		
// Define Access Rules

// Define link rules for sidebar related view
	
	}

	public function getTotals ($_project_id='', $_task_id='') {
		
		
		if (empty($_project_id))
		{
			return false;
		}
		
		$cc=new ConstraintChain();
				
		$cc->add(new Constraint('project_id', '=', $_project_id));

		if (!empty($_task_id))
		{
			$cc->add(new Constraint('task_id', '=', $_task_id));
		}
		
		$budget_summary=array();
		foreach ($this->getEnumOptions('budget_item_type') as $key=>$type)
		{
			$cc1=new ConstraintChain();
			$cc1->add($cc);
			$cc1->add(new Constraint('budget_item_type', '=', $key));
			$totals=$this->getSumFields(
					array(
							'setup_cost',
							'total_cost_rate',
							'setup_charge',
							'total_charge_rate'
						),
						$cc1
					);
			$budget_summary[$type]=$totals;
		}
		
		return $budget_summary;
	}
	
}
?>
