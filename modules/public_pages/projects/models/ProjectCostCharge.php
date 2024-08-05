<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectCostCharge extends DataObject {
	
	protected $version='$Revision: 1.4 $';
	
	public function __construct($tablename='project_costs_charges', $item_type='', $source_type='') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->identifierField='item_id';

// Define relationships
		$this->belongsTo('Project', 'project_id');
		$this->belongsTo('Task', 'task_id');
		$this->belongsTo('STItem', 'stitem_id');

// Define field formats

// The name for these links is determined from the source and type on the GL Transaction
		switch (strtoupper((string) $item_type)) {
			case 'PO' :
				$this->hasOne('POrder', 'item_id', 'po');
				break;
			case 'SI' :
				$this->hasOne('SInvoice', 'item_id', 'si');
				break;
		}
/*		
		switch (strtoupper($source_type)) {
			case 'O' :
				$this->hasOne('ProjectBudget', 'source_id', 'o');
				break;
			case 'E' :
				$this->hasOne('ProjectEquipmentAllocation', 'source_id', 'e');
				break;
			case 'M' :
				$this->hasOne('ProjectBudget', 'source_id', 'm');
				break;
			case 'R' :
				$this->hasOne('ProjectResource', 'source_id', 'r');
				break;
			case 'X' :
				$this->hasOne('ExpenseLine', 'source_id', 'x');
				break;
		}
*/		
// Define field defaults

// Define validation

// Define enumerated types
		$this->setEnum('item_type'
							,array('PO'=>'Costs'
								  ,'SI'=>'Charges'
								)
						);

		$this->setEnum('source_type'
							,array('O'=>'Other'
								  ,'B'=>'Budget'
								  ,'E'=>'Equipment'
								  ,'M'=>'Materials'
								  ,'R'=>'Resources'
								  ,'X'=>'Expenses'
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
		
		$costs_charges=array();
		foreach ($this->getEnumOptions('item_type') as $key=>$type)
		{
			$cc1=new ConstraintChain();
			$cc1->add($cc);
			$cc1->add(new Constraint('item_type', '=', $key));
			
			switch ($key) {
				case 'PO':
					$subkey='total_costs';
					$tablename='project_purchase_orders';
					break;
				case 'SI':
					$subkey='total_invoiced';
					$tablename='project_sales_invoices';
					break;
			}
			
			$totals=$this->getSumFields(
					array(
							'net_value'
						),
						$cc1,
						$tablename
					);
			$costs_charges[$subkey]=$totals['net_value'];
		}
		
		return $costs_charges;
	}
	
}
?>