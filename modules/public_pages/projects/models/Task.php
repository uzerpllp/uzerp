<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Task extends DataObject {
	
	protected $version='$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'parent'
										   ,'start_date'
										   ,'end_date'
										   ,'progress'
										   ,'milestone'
										   ,'deliverable'
										   );
	
	protected $linkRules;
										 
	function __construct($tablename='tasks') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';

		$this->orderby='start_date';
		$this->identifierField='name';

// Define relationships
		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Task','parent_id','parent');
		$this->belongsTo('User', 'owner', 'task_owner');
 		$this->belongsTo('User', 'altered_by', 'altered');
 		$this->belongsTo('Taskpriority', 'priority_id', 'priority');
		$this->hasMany('Task','tasks','parent_id');		
		$this->hasMany('Hour','hours');
		$this->hasMany('Expense','expenses');
// 		$this->hasMany('Resource','resources');
		$this->hasMany('taskattachment','attachments');
//		$this->hasMany('ProjectEquipmentAllocation', 'equipment_allocations', 'task_id');
		//$this->hasMany('ProjectCostCharge', 'purchase_orders', 'task_id');
		//$this->hasMany('ProjectCostCharge', 'sales_invoices', 'task_id');
		$this->hasMany('POrder','porders');
		$this->hasMany('PInvoice','pinvoices');
		$this->actsAsTree();
		$this->setParent();
 		
// Define field formats
		$this->getField('progress')->setFormatter(new PercentageFormatter());
		$this->getField('duration')->setFormatter(new IntervalFormatter());
		
// Define field defaults
		$this->getField('start_date')->setDefault(mktime(SystemCompanySettings::DAY_START_HOURS,SystemCompanySettings::DAY_START_MINUTES));
		
// Define validation
		$this->getField('duration')->blockValidator('NumericValidator');
		
// Define enumerated types
		$this->setEnum('progress',getRange(0,100,10,true,'',''));
		
// Define Access Rules

// Define link rules for sidebar related view
		$this->linkRules=array('expenses'=>array('modules'=>array('link'=>array('module'=>'hr')
																 ,'new'=>array('module'=>'hr'))
											 ,'actions'=>array('link','new')
											 ,'rules'=>array()
											 ),
									  'porders'=>array('modules'=>array('link'=>array('module'=>'purchase_order')
																 ,'new'=>array('module'=>'purchase_order'))
												,'actions'=>array('link','new')
												,'rules'=>array()
												,'label'=>'Show Purchase Orders'
												),
										'pinvoices'=>array('modules'=>array('link'=>array('module'=>'purchase_invoicing')
																 ,'new'=>array('module'=>'purchase_invoicing'))
												,'actions'=>array('link','new')
												,'rules'=>array()
												,'label'=>'Show Purchase Invoices'
												)
							);
		
	}


	/**
	 * Extend save to update properties of parent-tasks
	 */
	public function save($debug=false) {
		$res = parent::save($debug);
		$p_id = $this->parent_id;
		if($res===false || empty($p_id)) {
			return $res;
		}
		$this->updateParent();
		return true;
	}
	
	private function updateParent() {
		$p_id = $this->parent_id;
		if(!empty($p_id)) {
			$parent = new Task();
			$parent->load($p_id);
			$parent->updateProperties();
		}
	}
	/**
	 * Tasks with subtasks take on the start_date, end_date, duration and progress based on their children
	 * - earliest start_date
	 * - latest end_date
	 * - sum(duration)
	 * - (sum(progress*duration))/sum(duration) for progress
	 */
	public function updateProperties() {
		$db = DB::Instance();
		$query = 'SELECT t.start_date FROM tasks t WHERE t.parent_id='.$db->qstr($this->id).' ORDER BY start_date ASC';
		$this->start_date = $db->GetOne($query);
		
		$query = 'SELECT t.end_date FROM tasks t WHERE t.parent_id='.$db->qstr($this->id).' ORDER BY end_date DESC';
		$this->end_date = $db->GetOne($query);
		
		$query = 'SELECT sum(duration) AS duration FROM tasks t WHERE t.parent_id='.$db->qstr($this->id);
		$this->duration=$db->GetOne($query);
		
		
		$query = 'SELECT coalesce(
					(
						sum(
							(progress::float/100)*(extract(hours from duration))
						)
					)
					/
					(
						sum(
							extract (hours from duration)
						)
					)
				,0)*100 AS progress FROM tasks t WHERE parent_id='.$db->qstr($this->id);
		
		$this->progress = $db->GetOne($query);
		
		$this->save();
		$this->updateParent();
	}

	public function getEnum($name,$val) {
		if($name=='progress') {
			return $val;
		}
		return parent::getEnum($name,$val);
	}

	public function complete() {
		if ($this->_loaded) {
			$this->update($this->id,array('end_date','progress'),array('(now())',100));
			$this->updateParent();
		}
	}
	
	public function getChildrenAsDOC($doc=null,$sh=null) {
		if($doc ==null) {
			$doc = new TaskCollection($this);
		}
		if($sh==null) {
			$sh = new SearchHandler($doc,false);
			$sh->setOrderBy('start_date');
		}
		return parent::getChildrenAsDOC($doc,$sh);
	}

	public function getExpensesTotals () {
		
		$expenses=new Expense();
		$cc=new ConstraintChain();
				
		$cc->add(new Constraint('task_id', '=', $this->id));

		$totals=$expenses->getSumFields(
					array(
							'net_value'
						),
						$cc
					);

		return $totals;
	}
	
	public function getHourTotals () {
		
		$tasks=new TaskCollection(new Task());
		$sh=new SearchHandler($tasks, false);
		$tasks->getTaskHourTotals($sh, $this->id);
		$tasks->load($sh);
		
		$costs=array('total_hours'=>0, 'total_costs'=>0);
		foreach ($tasks as $task) {
			$time = explode(':', $task->total_hours);
			$hours = $time[0]+$time[1]/60+$time[2]/3600;
			$costs['total_hours'] += $hours;
			$costs['total_costs'] += $hours*$task->resource_rate;
		}
		//echo '<pre>'.print_r($costs, true).'</pre><br>';
		return $costs;
	}
	
	public function getMaterialsTotals () {

// do not have task_id on PO/SO
// probably need new table to link project/task to PO/SO
		$orders=new POrder();
		$cc=new ConstraintChain();
				
		$cc->add(new Constraint('project_id', '=', $this->id));

		$totals['Materials']['total_costs']=$orders->getSumFields(
					array(
							'net_value'
						),
						$cc
					);

		$orders=new SOrder();
		$cc=new ConstraintChain();
				
		$cc->add(new Constraint('project_id', '=', $this->id));

		$totals['Materials']['total_charges']=$orders->getSumFields(
					array(
							'net_value'
						),
						$cc
					);

		return $totals;
		
	}
	
}
?>
