<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ResourcesController extends Controller {

	protected $version='$Revision: 1.3 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Resource();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$s_data = null;
		$errors=array();

		$this->setSearch('ProjectSearch', 'resources', $s_data, $errors);

		$this->view->set('clickaction', 'edit');
		
		parent::index($pi=new ResourceCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'new_project_resource'
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
	
	public function _new() {
		
		$resource=$this->_uses[$this->modeltype];
		
		if (!$resource->isLoaded()) {
			if (empty($this->_data['project_id']))
			{
				$project=new Project();
				$projects=$project->getAll();
				$project_id=key($projects);
			}
			else
			{
				$project_id=$this->_data['project_id'];
			}
			$this->view->set('project_id', $project_id);
			$tasks= $this->getTaskList($project_id);
			$dates=$this->getStartEndDate($project_id);
			$this->view->set('start_date', $dates['start_date']['data']);
			$this->view->set('end_date', $dates['end_date']['data']);
		}
		else
		{
			$tasks= $this->getTaskList($resource->project_id);
		}
		$this->view->set('tasks', $tasks);
		
		$person=new Person();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', COMPANY_ID));
		$this->view->set('people', $person->getAll($cc));
		
		parent::_new();
		
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		
		$flash=Flash::Instance();
		$errors=array();
		
		$data=$this->_data[$this->modeltype];

		$obj='';
		if (!empty($data['task_id'])) {
			$obj=new Task();
			$obj->load($data['task_id']);
		}
		elseif (!empty($data['project_id'])) {
			$obj=new Project();
			$obj->load($data['project_id']);
		}
		
		if ($obj instanceof DataObject && $obj->isLoaded())
		{
			if (fix_date($data['start_date']) < $obj->start_date)
			{
				$errors['start_date']='Start date before '.get_class($obj).' start date';
			}
			if (fix_date($data['end_date']) > $obj->end_date)
			{
				$errors['end_date']='End date after '.get_class($obj).' end date';
			}
		}
		
		if (!empty($data['person_id']) && $data['quantity'] > 1)
		{
			$errors['person_id']='Quantity must be 1 for a person';
		}
		
		if(count($errors)==0 && parent::save($this->modeltype, '', $errors))
			sendBack();
		else {
			$flash->addErrors($errors);
			$this->refresh();
		}
		
	}
	
	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$resource=$this->_uses[$this->modeltype];
		$this->view->set('model',$resource);
		
		$detail=new MFResource();
		$detail->load($resource->resource_id);
		$resource->setAdditional('resource_rate');
		$resource->resource_rate = $detail->resource_rate;
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'new_project_resource'
				),
				'edit'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$resource->id),
					'tag'=>'edit'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	public function viewProject() {
		$this->view->set('page_title',$this->getPageName('Project Resources', 'View'));
		$this->viewrelated('viewProject');
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
			$start_date	= un_fix_date($obj->start_date);
			$end_date	= un_fix_date($obj->end_date);
		}
		else
		{
			$start_date	= $end_date	= date(DATE_FORMAT);
		}
		
		$output['start_date']=array('data'=>$start_date,'is_array'=>is_array($start_date));
		$output['end_date']=array('data'=>$end_date,'is_array'=>is_array($end_date));
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $output;
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
	
	
/* Protected Functions */
	protected function getPageName($base=null, $action=null) {
		return parent::getPageName((!empty($base))?$base:'project_resources',$action);
	}

/* Private Functions */

}
?>
