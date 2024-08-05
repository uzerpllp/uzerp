<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectbudgetsController extends Controller {

	protected $version='$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		
		$this->_templateobject = new ProjectBudget();
		$this->uses($this->_templateobject);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null) {
		$this->view->set('clickaction', 'view');
		
		parent::index(new ProjectBudgetCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Project Budget'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}
	
	public function _new() {
		parent::_new();
		
		$budget=$this->_uses[$this->modeltype];
		
		if (!$budget->isLoaded())
		{
			if (!empty($this->_data['project_id']))
			{
				$budget->project_id=$this->_data['project_id'];
			}
			$budget->budget_item_type=key($budget->getEnumOptions('budget_item_type'));
		}

		if (is_null($budget->project_id))
		{
			$project=new Project();
			$projects=$project->getAll();
			$this->view->set('projects', $projects);
			$project_id=key($projects);
		}
		else
		{
			$project_id=$budget->project_id;
		}
		
		$this->view->set('tasks', $this->getTaskList($project_id));
				
		$this->view->set('items', $this->getBudgetItemList($budget->budget_item_type));
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$errors=array();
		
		if (isset($this->_data[$this->modeltype]['quantity']) && isset($this->_data[$this->modeltype]['cost_rate']))
		{
			$this->_data[$this->modeltype]['total_cost_rate']=bcmuL((string) $this->_data[$this->modeltype]['quantity'],(string) $this->_data[$this->modeltype]['cost_rate']);
		}
		
		if (isset($this->_data[$this->modeltype]['quantity']) && isset($this->_data[$this->modeltype]['charge_rate']))
		{
			$this->_data[$this->modeltype]['total_charge_rate']=bcmuL((string) $this->_data[$this->modeltype]['quantity'],(string) $this->_data[$this->modeltype]['charge_rate']);
		}
		
		if(parent::save($this->modeltype, '' ,$errors))
		{
			if (!empty($this->_data[$this->modeltype]['task_id']))
			{
				$controller='tasks';
				$id=$this->_data[$this->modeltype]['task_id'];
			}
			elseif (!empty($this->_data[$this->modeltype]['project_id']))
			{
				$controller='projects';
				$id=$this->_data[$this->modeltype]['project_id'];
			}
			sendTo($controller, 'view', $this->_modules, array('id'=>$id));
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}
	}
	
	public function view() {

		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$budget=$this->_uses[$this->modeltype];
		$this->view->set('model',$budget);
		$total_cost=$budget->setup_cost+$budget->total_cost_rate;
		$total_charge=$budget->setup_charge+$budget->total_charge_rate;
 
 		$sidebar=new SidebarController($this->view);
		$sidebar->addList(
			'currently_viewing',
			array(
				$budget->name => array(
					'tag' => $budget->name,
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'view'
								   ,'id'=>$budget->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'edit'
								   ,'id'=>$budget->id)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'delete'
								   ,'id'=>$budget->id)
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function viewProject() {
		$this->view->set('page_title',$this->getPageName('Project Budgets', 'View'));
		$this->viewrelated('viewProject');
	}
	
/* Ajax functions */
	public function getItemDetail($_budget_item_id='', $_budget_item_type='') {
		
		if(!empty($this->_data['budget_item_id'])) { $_budget_item_id=$this->_data['budget_item_id']; }
		if(!empty($this->_data['budget_item_type'])) { $_budget_item_type=$this->_data['budget_item_type']; }
		
		$uom_id='';
		switch ($_budget_item_type) {
			case 'R':
				$budgetitem=new SOProductline();				
				$budgetitem->load($_budget_item_id);
				$uom_id=$budgetitem->stuom_id;
				$cost_rate=0;
				$setup_cost=0;				
				$charge_rate=$budgetitem->price;
				$setup_charge=0;
				break;			
			case 'E':
				$budgetitem=new ProjectEquipment();
				$budgetitem->load($_budget_item_id);
				$uom_id=$budgetitem->uom_id;
				$cost_rate=$budgetitem->cost_rate;
				$setup_cost=$budgetitem->setup_cost;
				$charge_rate=0;
				$setup_charge=0;
				break;
			case 'M':
				$budgetitem=new STItem();
				$budgetitem->load($_budget_item_id);
				$uom_id=$budgetitem->uom_id;
				$cost_rate=$budgetitem->latest_cost;
				$setup_cost=0;
				$charge_rate=0;
				$setup_charge=0;
				break;
			case 'L':
				$budgetitem=new MFResource();
				$budgetitem->load($_budget_item_id);
				$cost_rate=$budgetitem->resource_rate;
				$uom_id=11;
				$setup_cost=0;
				$charge_rate=0;
				$setup_charge=0;
				break;
			default:
				$budgetitem=false;
		}
		
		if ($budgetitem && $budgetitem->isLoaded())
		{
			$output['description']=array('data'=>$budgetitem->getIdentifierValue(),'is_array'=>false);
			$output['uom_id']=array('data'=>$uom_id,'is_array'=>false);
			$output['cost_rate']=array('data'=>$cost_rate,'is_array'=>false);
			$output['setup_cost']=array('data'=>$setup_cost,'is_array'=>false);
			$output['charge_rate']=array('data'=>$charge_rate,'is_array'=>false);
			$output['setup_charge']=array('data'=>$setup_charge,'is_array'=>false);
		}
		else
		{
			$output['description']=array('data'=>'','is_array'=>false);
			$output['uom_id']=array('data'=>'','is_array'=>false);
			$output['cost_rate']=array('data'=>0,'is_array'=>false);
			$output['setup_cost']=array('data'=>0,'is_array'=>false);			
			$output['charge_rate']=array('data'=>0,'is_array'=>false);
			$output['setup_charge']=array('data'=>0,'is_array'=>false);
		}
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $output;
		}
	}
	
	public function getBudgetItemList($_budget_item_type='') {
		
		if(!empty($this->_data['budget_item_type'])) { $_budget_item_type=$this->_data['budget_item_type']; }
				
		switch ($_budget_item_type) {
			case 'R':
				$budgetitem=new SOProductline();
				break;
			case 'E':
				$budgetitem=new ProjectEquipment();
				break;
			case 'M':
				$budgetitem=new STItem();
				break;
			case 'L':
				$budgetitem=new MFResource();
				break;
			default:
				$budgetitem='none';
		}

		if ($budgetitem!='none')
		{
			$budgetitems=$budgetitem->getAll();
		}
		else
		{
			$budgetitems=array();
		}
		
		if(isset($this->_data['ajax'])) {
			$output['budget_item_id']=array('data'=>$budgetitems,'is_array'=>is_array($budgetitems));
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $budgetitems;
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