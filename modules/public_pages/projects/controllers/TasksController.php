<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TasksController extends Controller {
	
	protected $version='$Revision: 1.18 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Task();
		$this->uses($this->_templateobject);

	}
	
	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new TaskCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_task'
				)
			)
		);
		$this->view->set('no_delete',true);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new() {
		
		parent::_new();

		$task=$this->_uses[$this->modeltype];

		$project_id = '';
		$task_id='';
		
		if(isset($this->_data['parent_id']))
		{
			$parenttask=new Task();
			$parenttask->load($this->_data['parent_id']);
			$project_id=$this->_data['project_id']=$task->project_id=$parenttask->project_id;
			$task_id=$this->_data['parent_id'];
		} 
		elseif(isset($this->_data['project_id']))
		{
			$project_id = $this->_data['project_id'];
		}
		elseif ($task->isLoaded())
		{
			$project_id = $task->project_id;
			$task_id=$task->id;
		}
			
		$this->view->set('readonly', false);
		
		if (!$task->isLoaded()) {
			$dates=$this->getStartEndDate($project_id, $task_id);
			$this->view->set('start_date', $dates['start_date']);
			$this->view->set('end_date', $dates['end_date']);
		}
		elseif (count($task->getChildren())>0)
		{
			$this->view->set('readonly', true);
		}
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('milestone','=','false'));
		
		if(isset($this->_data['id'])) {
			$cc->add(new Constraint('id','<>',$this->_data['id']));
		}
		if(!empty($project_id)) {
			$cc->add(new Constraint('project_id', '=', $project_id));
			$this->_templateobject->project_id = $project_id;
		}
		
		$tasks=$task->getAll($cc);
		
		$this->view->set('parent_tasks',$tasks);
	
	}
	
	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$task=$this->_uses[$this->modeltype];
		
		if(!$task->isLoaded()) {
			$flash=Flash::Instance();
			$flash->addError("The selected task does not exist");
			sendTo('tasks','index',array('projects'));
		}
	
		$sidebar=new SidebarController($this->view);
//		$sidebar->addList(
//			'Actions',
//			array(
//				'new_task'=>array(
//					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
//					'tag'=>'new_task'
//				)
//			)
//		);
		$sidebar->addList(
			'currently_viewing',
			array(
				$task->name => array(
					'tag' => $task->name,
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'view','id'=>$task->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'edit','id'=>$task->id)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'delete','id'=>$task->id)
				),
				'mark_as_complete' => array(
					'tag' => 'Mark as Complete',
					'link' => array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'complete','id'=>$task->id)
				)
			)
		);
//		$sidebar->addList(
//			'Calendars',
//			array(
//				'day_view'=>array(
//					'link'=>array('module'=>'calendar','controller'=>'index','action'=>'dayview'),
//					'tag'=>'day_view'
//				),
//				'week_view'=>array(
//					'link'=>array('module'=>'calendar','controller'=>'index','action'=>'weekview'),
//					'tag'=>'week_view'
//				),
//				'month_view'=>array(
//					'link'=>array('module'=>'calendar','controller'=>'index','action'=>'monthview'),
//					'tag'=>'month_view'
//				)
//			)
//		);
		
		$this->sidebarRelatedItems($sidebar, $task);

		$sidebarlist['view_task_totals']= array('tag'=>'view_task_totals'
												  ,'link'=> array('modules'=>$this->_modules
												  				 ,'controller'=>$this->name
												  				 ,'action'=>'viewproject_totals'
												  				 ,'id'=>$task->id)
															);
		
		$sidebar->addList('related_items',$sidebarlist);
		
// Need to add equipment hours for tasks that use equipment
// This needs a link rule?
//		$equipment_id = $task->equipment_id;
//		if (isset($equipment_id)) {
//			$related_items['equipmenthours'] = array(
//				'tag'=>'Equipment Hours',
//				'link'=>array('module'=>'projects','controller'=>'hours','action'=>'viewproject','project_id'=>$task->project_id),
//				'new'=>array('module'=>'projects','controller'=>'hours','action'=>'newequipment','project_id'=>$task->project_id,'task_id'=>$task->id)
//			);
//		}
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		if($task instanceof Task) {
			$pl = new PreferencePageList('recently_viewed_tasks');
			$pl->addPage(new Page(array('module'=>$this->_modules,'controller'=>$this->name,'action'=>'view','id'=>$task->id),'task',$task->name));
			$pl->save();
		}
	}

	public function complete() {
		if (isset($this->_data['id'])) {
			$flash = Flash::Instance();
			$flash->addMessage('Task marked as completed');
			$task = new Task();
			$task->load($this->_data['id']);
			$task->complete();
			sendBack();
		}
		
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('Task');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function save() {
		
		$flash=Flash::Instance();		
		$errors = array();
		
		if(isset($this->_data['Task']['start_date'])) {
			$start_date=$this->_data['Task']['start_date'];
			$start_date_hours=(!empty($this->_data['Task']['start_date_hours'])?$this->_data['Task']['start_date_hours']:'00');
			$start_date_minutes=(!empty($this->_data['Task']['start_date_minutes'])?$this->_data['Task']['start_date_minutes']:'00');
			$this->_data['Task']['start_date']=$start_date.' '.$start_date_hours.':'.$start_date_minutes;
		}
		
		if(isset($this->_data['Task']['end_date']) && $this->_data['Task']['end_date']!='') {
			$end_date=$this->_data['Task']['end_date'];
			$end_date_hours=(!empty($this->_data['Task']['end_date_hours'])?$this->_data['Task']['end_date_hours']:'00');
			$end_date_minutes=(!empty($this->_data['Task']['end_date_minutes'])?$this->_data['Task']['end_date_minutes']:'00');
			$this->_data['Task']['end_date']=$end_date.' '.$end_date_hours.':'.$end_date_minutes;
		} else {
			if(isset($this->_data['Task']['start_date'])) {
				$this->_data['Task']['end_date']=$this->_data['Task']['start_date'];
			}
		}
		
		if (!empty($this->_data['Task']['project_id'])) {
			$project=new Project();
			$project->load($this->_data['Task']['project_id']);
			
			if ($project->isLoaded())
			{
				if (strtotime(fix_date($start_date)) < strtotime($project->start_date))
				{
					$errors['start_date']='Start date before Project start date';
				}

				if (strtotime(fix_date($end_date)) > strtotime($project->end_date))
				{
					$errors['end_date']='End date after Project end date';
				}
			}
		}
		
		if(!isset($this->_data['Task']['progress'])) {
			$this->_data['Task']['progress']=0;
		}
		
		if(isset($this->_data['Task']['duration']) && !empty($this->_data['Task']['duration'])) {
			if($this->_data['Task']['duration_unit']=='days') {
				$this->_data['Task']['duration'] = $this->_data['Task']['duration'] * SystemCompanySettings::DAY_LENGTH;
			}
			$this->_data['Task']['duration'].=' hours';
		}
		
		if(!isset($this->_data['Task']['equipment_id']) || !is_numeric($this->_data['Task']['equipment_id'])) {
			$this->_data['Task']['equipment_hourly_cost'] = null;
			$this->_data['Task']['equipment_setup_cost'] = null;
		} else if (isset($this->_data['Task']['equipment_id']) && is_numeric($this->_data['Task']['equipment_id'])) {
			if (!is_numeric($this->_data['Task']['equipment_hourly_cost']) && !is_numeric($this->_data['Task']['equipment_setup_cost'])) {
				// Autocomplete
				$equipment = new ProjectEquipment();
				$equipment->load($this->_data['Task']['equipment_id']);
				$this->_data['Task']['equipment_hourly_cost'] = $equipment->hourly_cost;
				$this->_data['Task']['equipment_setup_cost'] = $equipment->setup_cost;
			}
		}
		
		$db=DB::Instance();
		
		if (!isset($this->_data['Task']['ignore']) && isset($this->_data['Task']['equipment_id']) && $this->_data['Task']['equipment_id']!='') {
			$period = array(
				'start_date' => $this->_data['Task']['start_date'],
				'end_date' => $this->_data['Task']['end_date']
			);
		
			$q = "
			SELECT
				(t.start_date, t.end_date)
			OVERLAPS
				(DATE " . $db->qstr($period['start_date']) . ", DATE " . $db->qstr($period['end_date']) . ")
			AS
				conflict
			FROM
				tasks t
			WHERE
				t.equipment_id=" . $db->qstr($this->_data['Task']['equipment_id']) . "
			GROUP BY
				conflict
			;";
		
			$conflicts = $db->GetArray($q);

			if (count($conflicts) == 2 || $conflicts[0]['conflict'] == 't') {
				$q = "SELECT name FROM project_equipment e WHERE e.id = " . $db->qstr($this->_data['Task']['equipment_id']) . ";";
				$name = $db->GetOne($q);
				$flash->addError($name . ' is already allocated to another task at this time.');
				$this->_data['id'] = $this->_data['Task']['id'];
				$this->view->set('conflict', 'true');
				$this->edit();
				return;
			}
		}

		if(count($errors) == 0 && parent::save('Task', '', $errors)) {
			if(isset($this->_data['Task']['referrer_view'])) {
				sendTo('index',$this->_data['Task']['referrer_view'],array('calendar'));	
			} else {
				if(isset($this->_data['original_action']) && in_array($this->_data['original_action'],array('dayview','weekview','monthview'))) {
					sendTo('index',$this->_data['original_action'],'calendar');
 				} else {
					sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
 				}
			}
		} else {
			$flash->addErrors($errors);
			$this->_data['id']=$this->_data['Task']['id'];
			$this->_data['project_id']=$this->_data['Task']['project_id'];
			$this->refresh();
		}

	}
	
	public function updateTask() {
		if (date('i',$this->_data['start_time']) == '02') {
			$this->_data['start_time'] = $this->_data['start_time']-120;
		}
		$this->_data['duration'] = (round(($this->_data['duration']/60)/15)*15)*60;
		$start_time = date('Y-m-d H:i:00', $this->_data['start_time']);
		$end_time = date('Y-m-d H:i:00', $this->_data['start_time']+$this->_data['duration']);
		$tc = $this->_uses['Task'];
		$tc->load($this->_data['id']);
		$tc->start_date = $start_time;
		$tc->end_date = $end_time;
		$tc->save();
		echo date('H:i',$this->_data['start_time']).'-'.date('H:i', $this->_data['start_time']+$this->_data['duration']);
		exit;
	}

	public function viewequipment_allocations() {
		
		$equipment=new ProjectEquipmentAllocation();
		$equipmentallocation=new ProjectEquipmentAllocationCollection($equipment);
		$sh=$this->setSearchHandler($equipmentallocation);
		$sh->setFields(array('id'
							,'equipment'
							,'task'
							,'start_date'
							,'end_date'
							,'charging_period_uom'
							,'setup_charge'
							,'charge_rate'
							,'charge_rate_uom'
							));
		$sh->addConstraint(new Constraint('task_id', '=', $this->_data['id']));
		
		parent::index($equipmentallocation, $sh);
		
		$this->view->set('page_title', $this->getPageName('', 'view equipment_allocations'));
	}
	
	public function viewproject_totals () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
				
		$task=$this->_uses[$this->modeltype];
		
		$projectbudgets=new ProjectBudget();
		$budget_totals=$projectbudgets->getTotals($task->project_id, $task->id);
		
		$expenses = $task->getExpensesTotals();
		$budget_totals['Other']['total_costs']=$expenses['net_value'];
		
		$projectcostscharges=new ProjectCostCharge();
		$budget_totals['Materials']=array_merge_recursive($budget_totals['Materials']
														, $projectcostscharges->getTotals($task->project_id, $task->id));
		
		$projectequipment=new ProjectEquipmentAllocation();
		$budget_totals['Equipment']=array_merge_recursive($budget_totals['Equipment']
														, $projectequipment->getChargeTotals($task->project_id, $task->id));
	
		$budget_totals['Labour']=array_merge_recursive($budget_totals['Labour']
														, $task->getHourTotals());
														
		$this->view->set('budget_totals', $budget_totals);
	
	}
	
	public function viewpurchase_orders () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$task=$this->_uses[$this->modeltype];
		
		$porders=new ProjectCostChargeCollection(new ProjectCostCharge('project_purchase_orders', 'PO'));
		$sh=$this->setSearchHandler($porders);
		$sh->addConstraint(new Constraint('task_id', '=', $task->id));
		$sh->setFields(array('order_id', 'order_number', 'order_date', 'supplier', 'net_value', 'due_date'));
		parent::index($porders, $sh);
		
		$this->setTemplateName('view_project_costs_charges');
	
	}
	
	public function viewsales_invoices () {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$task=$this->_uses[$this->modeltype];
		
		$sinvoices=new ProjectCostChargeCollection(new ProjectCostCharge('project_sales_invoices', 'SI'));
		$sh=$this->setSearchHandler($sinvoices);
		$sh->addConstraint(new Constraint('task_id', '=', $task->id));
		$sh->setFields(array('invoice_id', 'invoice_number', 'invoice_date', 'customer', 'net_value', 'tax_value', 'gross_value'));
		parent::index($sinvoices, $sh);
		
		$this->setTemplateName('view_project_costs_charges');
	
	}
	
	
/* Ajax functions */
	public function getStartEndDate ($_project_id='', $_task_id='') {
		
		if(!empty($this->_data['project_id'])) { $_project_id=$this->_data['project_id']; }
		if(!empty($this->_data['task_id'])) { $_task_id=$this->_data['task_id']; }
		
		$obj='';
		if (!empty($_task_id))
		{
			$obj=new Task();
			$obj->load($_task_id);
		}
		elseif (!empty($_project_id))
		{
			$obj=new Project();
			$obj->load($_project_id);
		}
		
		if ($obj instanceof DataObject && $obj->isLoaded())
		{
			$start_date	= un_fix_date($obj->start_date, true);
			$end_date	= un_fix_date($obj->end_date, true);
		}
		else
		{
			$start_date	= $end_date	= date(DATE_FORMAT).' 00:00';
		}

		$dates = array('start_date'=>$start_date
					  ,'end_date'=>$end_date);
					  
		$start_date_hours	= array_shift(explode(':', array_pop(explode(' ', $start_date))));
		$start_date_minutes	= array_pop(explode(':', array_pop(explode(' ', $start_date))));
		$start_date			= array_shift(explode(' ', $start_date));
		
		$end_date_hours		= array_shift(explode(':', array_pop(explode(' ', $end_date))));
		$end_date_minutes	= array_pop(explode(':', array_pop(explode(' ', $end_date))));
		$end_date			= array_shift(explode(' ', $end_date));
		
		$output['start_date']=array('data'=>$start_date,'is_array'=>is_array($start_date));
		$output['start_date_hours']=array('data'=>$start_date_hours,'is_array'=>is_array($start_date_hours));
		$output['start_date_minutes']=array('data'=>$start_date_minutes,'is_array'=>is_array($start_date_minutes));
		$output['end_date']=array('data'=>$end_date,'is_array'=>is_array($end_date));
		$output['end_date_hours']=array('data'=>$end_date_hours,'is_array'=>is_array($end_date_hours));
		$output['end_date_minutes']=array('data'=>$end_date_minutes,'is_array'=>is_array($end_date_minutes));
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $dates;
		}
	
	}

	public function getTaskList($_project_id='') {
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['project_id'])) { $_project_id=$this->_data['project_id']; }
		}
		
		if (!empty($_project_id)) {
			$depends=array('project_id'=>$_project_id);
		} else {
			$depends=array();
		}
		
		$tasks=$this->getOptions($this->_templateobject, 'parent_id', '', '', '', $depends);
		
		if(isset($this->_data['ajax'])) {
			echo $tasks;
			exit;
		} else {
			return $tasks;
		}
		
	}
	
}
?>
