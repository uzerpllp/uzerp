<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ProjectequipmentallocationsController extends Controller {

	protected $version='$Revision: 1.3 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ProjectEquipmentAllocation();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){

		parent::index($pi=new ProjectEquipmentAllocationCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'projects','controller'=>'ProjectEquipmentAllocations','action'=>'new'),
					'tag'=>'new_Project Equipment Allocation'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		sendBack();
	}
	
	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$projectequipmentallocation=$this->_uses[$this->modeltype];

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'projects','controller'=>'ProjectEquipmentAllocations','action'=>'new'),
					'tag'=>'new_project_issue'
				),
				'edit'=>array(
					'link'=>array('module'=>'projects','controller'=>'ProjectEquipmentAllocations','action'=>'edit','id'=>$projectequipmentallocation->id),
					'tag'=>'edit'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	public function _new() {

		parent::_new();
	
		$projectequipmentallocation=$this->_uses[$this->modeltype];
		
		$equipmentlist=$this->getOptions($this->_templateobject, 'project_equipment_id', 'getOptions', 'getOptions');

		$data=$this->_data[$this->modeltype] ?? array();
		
		if (!empty($this->_data['project_id']))	{ $data['project_id']=$this->_data['project_id']; }
		
		if (!empty($this->_data['task_id'])) { $data['task_id']=$this->_data['task_id']; }
		
		if (!empty($data['project_equipment_id']))
		{
			// this is set if there has been error and we are redisplaying the screen
			$default_equipment=$this->_data['project_equipment_id'];
		}
		else
		{
			if (!$projectequipmentallocation->isLoaded()) {
				$default_equipment=$this->getDefaultValue($this->modeltype, 'project_equipment_id', '');
			} else {
				$default_equipment=$projectequipmentallocation->project_equipment_id;
			}
		}
		if (empty($default_equipment)) {
			$default_equipment=key($equipmentlist);
		}

		$projectequipment=$this->getEquipmentDetail($default_equipment);
		$this->view->set('setup_charge', $projectequipment->setup_cost);
		$this->view->set('charge_rate', $projectequipment->hourly_cost);
		
		$project_id=(!empty($this->_data['project_id'])?$this->_data['project_id']:'');
		$task_id=(!empty($this->_data['task_id'])?$this->_data['task_id']:'');
		
		if (!$projectequipmentallocation->isLoaded())
		{
			$projectequipmentallocation->project_equipment_id=$default_equipment;
			if (!empty($data['task_id']) && empty($data['project_id']))
			{
				$task=new Task;
				$task->load($data['task_id']);
				if ($task->isLoaded())
				{
					$data['project_id']=$projectequipmentallocation->project_id=$task->project_id;
				}
			}
			$projectequipmentallocation->project_id=$data['project_id'];
			$dates=$this->getStartEndDate($data['project_id'], $data['task_id']);
			$this->view->set('start_date', $dates['start_date']);
			$this->view->set('end_date', $dates['end_date']);
		} else {
			$dates['start_date']	= un_fix_date($projectequipmentallocation->start_date);
			$dates['end_date']		= un_fix_date($projectequipmentallocation->end_date);
			$project_id				= $projectequipmentallocation->project_id;
		}
		
		$this->view->set('tasks', $this->getTaskList($project_id));
		
		$this->getEquipmentAllocation($default_equipment, $dates['start_date'], $dates['end_date']);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		
		$flash=Flash::Instance();
		if(parent::save($this->modeltype))
		{
			sendBack();
		}
		else
		{
			$this->refresh();
		}
		
	}
	
	protected function getPageName($base = \null, $action = \null) {
		return parent::getPageName('project_equipment_allocations');
	}

/* Ajax functions */
	public function getEquipmentDetail($_equipment_id) {
		
		if(!empty($this->_data['project_equipment_id'])) { $_equipment_id=$this->_data['project_equipment_id']; }
		
		$projectequipment=new ProjectEquipment();
		$projectequipment->load($_equipment_id);
		
		if(isset($this->_data['ajax'])) {
			$output['setup_charge']=array('data'=>$projectequipment->setup_cost,'is_array'=>false);
			$output['charge_rate']=array('data'=>$projectequipment->hourly_cost,'is_array'=>false);
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $projectequipment;
		}
	}
	
	public function getEquipmentAllocation($_equipment_id='', $_start_date='', $_end_date='') {
		
		if(!empty($this->_data['project_equipment_id'])) { $_equipment_id=$this->_data['project_equipment_id']; }
		if(!empty($this->_data['start_date'])) { $_start_date=$this->_data['start_date']; }
		if(!empty($this->_data['end_date'])) { $_end_date=$this->_data['end_date']; }
		
		$projectallocation=new ProjectEquipmentAllocation();
		$allocationdates=new ProjectEquipmentAllocationCollection($projectallocation);
		$sh=new SearchHandler($allocationdates);
		$sh->addConstraint(new Constraint('project_equipment_id', '=', $_equipment_id));
		$cc=new ConstraintChain();
		$cc->add(new Constraint('start_date', 'between', "'".fix_date($_start_date)."' and '".fix_date($_end_date)."'"));
		$cc->add(new Constraint('end_date', 'between', "'".fix_date($_start_date)."' and '".fix_date($_end_date)."'"), 'OR');
		$sh->addConstraintChain($cc);
		$sh->setFields(array('id', 'project', 'task', 'start_date', 'end_date'));
		$sh->setOrderBy('start_date');
		$allocationdates->load($sh, false);
		
		$this->view->set('no_ordering', true);
		$this->view->set('collection',$allocationdates);
		$this->view->set('showheading',true);
		
		if(isset($this->_data['ajax'])) {
			$this->setTemplateName('datatable_inline');
		} else {
			return $allocationdates;
		}
	}
	
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
			$start_date	= un_fix_date($obj->start_date);
			$end_date	= un_fix_date($obj->end_date);
		}
		else
		{
			$start_date	= $end_date	= date(DATE_FORMAT);
		}

		if(isset($this->_data['ajax'])) {
			$output['start_date']=array('data'=>$start_date,'is_array'=>is_array($start_date));
			$output['end_date']=array('data'=>$end_date,'is_array'=>is_array($end_date));
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return array('start_date'=>$start_date
						,'end_date'=>$end_date);
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
		
		$tasks=$this->getOptions($this->_templateobject, 'task_id', '', '', '', $depends);
		
		if(isset($this->_data['ajax'])) {
			echo $tasks;
			exit;
		} else {
			return $tasks;
		}
		
	}
	
}
?>
