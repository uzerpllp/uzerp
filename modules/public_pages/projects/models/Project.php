<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Project extends DataObject {
	
	protected $version='$Revision: 1.13 $';
	
	protected $defaultDisplayFields=array('job_no',
										 'name',
										 'company',
										 'person',
										 'end_date',
										 'status',
										 );
	
	protected $linkRules;
										 
	function __construct($tablename='projects') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField='job_no';		
		$this->orderby='job_no';
		
// Define relationships
		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('User', 'owner', 'project_owner');
 		$this->belongsTo('User', 'altered_by', 'altered');
 		$this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
		$cc=new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', EGS_COMPANY_ID));
 		$this->belongsTo('Person','key_contact_id','key_contact', $cc, "surname || ', ' || firstname");
 		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
 		$this->belongsTo('Projectcategory', 'category_id', 'category');
 		$this->belongsTo('Projectworktype', 'work_type_id', 'work_type');
		$this->belongsTo('Projectphase', 'phase_id', 'phase'); 
		$this->hasMany('ProjectBudget','budgets');
		$this->hasMany('Task','tasks');
		$this->hasMany('ProjectIssue','issues');
		$this->hasMany('ProjectNote','notes');
		$this->hasMany('Hour','hours');
		$this->hasMany('Expense','expenses');
		$this->hasMany('Resource','resources');
		// Note: 'projectattachment' model does not exist - it is here to generate
		// the sidebar related link to projectattachmentcontroller
		$this->hasMany('projectattachment','attachments');
		$this->hasMany('LoggedCall','calls');
		$this->hasMany('ProjectEquipmentAllocation', 'equipment_allocations', 'project_id');
		$this->hasMany('ProjectCostCharge', 'actuals', 'project_id');
//		$this->hasMany('ProjectCostCharge', 'purchase_orders', 'project_id');
//		$this->hasMany('ProjectCostCharge', 'sales_invoices', 'project_id');
		
		$tasks=new TaskCollection(new Task);
		$sh = new SearchHandler($tasks,false);
		$sh->addConstraint(new Constraint('parent_id',' is ','NULL'));
		$sh->setOrderBy('start_date');
		$this->addSearchHandler('tasks',$sh);
 		
// Define field formats
		
// Define field defaults
		$this->getField('status')->setDefault('N');
		
// Define validation
		$this->validateUniquenessOf(array("job_no"));

// Define enumerated types
 		$this->setEnum('status'
							,array('N'=>'New'
								  ,'A'=>'Active'
								  ,'C'=>'Complete'
								  ,'X'=>'Cancelled'
								)
						);

// Define Access Rules
 		$this->setAccessControlled(true);

// Define link rules for sidebar related view
		$this->linkRules=array('expenses'=>array('modules'=>array('link'=>array('module'=>'hr')
																 ,'new'=>array('module'=>'hr'))
												,'actions'=>array('link','new')
												,'rules'=>array()
												)
							);
							
	}

	function progress($format=true) {
		$db = DB::Instance();
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
				,0)*100 AS progress FROM tasks t WHERE parent_id IS NULL AND project_id='.$db->qstr($this->id);
		$progress = $db->GetOne($query);
		if(!$format) {
			return intval($progress);
		}
		return intval($progress).'%';
	}
	
	function expected_progress($format=true) {
		$db = DB::Instance();
		$query = 'select (CURRENT_DATE-start_date)/(end_date-start_date)*100 from projects where id='.$db->qstr($this->id);
		$exp_progress = $db->GetOne($query);
		if(!$format) {
			return intval($exp_progress);
		}
		return intval($exp_progress).'%';
	}
	
	function duration() {
		$db = DB::Instance();
		$query = 'select sum(to_char(duration,\'HH24\')::float)/'.SystemCompanySettings::DAY_LENGTH.' AS duration from tasks where project_id='.$db->qstr($this->id);
		$duration = $db->GetOne($query);
		return $duration;
	}
	
	public function getMostRecentChange() {
		$db = DB::Instance();
		$query = 'SELECT lastupdated FROM tasks WHERE project_id='.$db->qstr($this->id).' ORDER BY lastupdated DESC';
		$time = $db->GetOne($query);
		return $time;
	}
	
	function opp_contact() {
		$db = DB::Instance();
		$query = 'SELECT firstname || \' \' || surname AS name FROM person p JOIN users u ON (u.person_id=p.id) JOIN opportunities o ON (o.assigned=u.username) WHERE o.id='.$this->opportunity_id;
		$name = $db->GetOne($query);
		return $name;
	}
	
	function rag_status($html=true) {
		$exp_progress=$this->expected_progress(false);
		if($html) {
			$formatter = new TrafficLightFormatter();
		}
		else {
			$formatter = new NullFormatter();
		}
	
		if($this->progress(false)<$exp_progress) {
			if($this->progress(false)<(0.95*$exp_progress)) {
				return $formatter->format('red');
			}
			return $formatter->format('amber');
		}
		return $formatter->format('green');
	}
	
	public static function getResourceUsers($_project_id) {
		$db = DB::Instance();

		$user=new User();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('person_id', 'in', '(select person_id from project_resources where project_id='.$db->qstr($_project_id).')'));
		
		return $user->getAll($cc);
	}	
		
	public static function getProjectPeople($_project_id) {
		$db = DB::Instance();

		$person=new Person();
		$person->identifierField="surname || ', ' || firstname";
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('person_id', 'in', '(select person_id from project_resources where project_id='.$db->qstr($_project_id).')'));
		
		return $person->getAll($cc);
	}	
		
	public function getExpensesTotals () {
		
		$expenses=new Expense();
		$cc=new ConstraintChain();
				
		$cc->add(new Constraint('project_id', '=', $this->id));

		$totals=$expenses->getSumFields(
					array(
							'net_value'
						),
						$cc
					);

		return array('total_costs'=>$totals['net_value']);
	}
	
	public function getHourTotals () {
		
		$projects=new ProjectCollection(new Project());
		$sh=new SearchHandler($projects, false);
		$projects->getProjectHourTotals($sh, $this->id);
		$projects->load($sh);
		
		$costs=array('total_hours'=>0, 'total_costs'=>0);
		foreach ($projects as $project) {
			$time = explode(':', $project->total_hours);
			$hours = $time[0]+$time[1]/60+$time[2]/3600;
			$costs['total_hours'] += $hours;
			$costs['total_costs'] += $hours*$project->resource_rate;
		}
		//echo '<pre>'.print_r($costs, true).'</pre><br>';
		return $costs;
	}
	
	public function getMaterialsTotals () {
		
// do not have project_id on SO
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