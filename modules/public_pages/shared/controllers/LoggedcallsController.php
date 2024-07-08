<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class LoggedcallsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new LoggedCall();
		$this->uses($this->_templateobject);

	}
	
	public function _new() {
		parent::_new();
		$this->setTemplateName('calls_new');
		$projects = $opportunities = $activities = null;
		if(isset($this->_data['person_id'])) {
			$person = new Person();
			$person->load($this->_data['person_id']);
			$this->_data['company_id']=$person->company_id;
			$projects = $person->projects;
			$opportunities = $person->opportunities;
			$activities = $person->activities;
			$this->view->set('person', $person->fullname);
		}
		
		if(isset($this->_data['company_id'])) {
			$company = new Company();
			$company->load($this->_data['company_id']);
			$projects = DataObjectCollection::Merge($company->projects,$projects);
			$opportunities = DataObjectCollection::Merge($company->opportunities,$opportunities);
			$activities = DataObjectCollection::Merge($company->activities,$activities);
			$people = new Person();
			$cc = new ConstraintChain;
			$cc->add(new Constraint('company_id', '=', $this->_data['company_id']));
			$this->view->set('people', $people->getAll($cc));
			$this->view->set('company', $company->name);
		}
		if(isset($this->_data['project_id'])) {
			$project = new Project();
			$project->load($this->_data['project_id']);
			$this->_data['company_id']=$project->company_id;
		}
		
		$this->view->set('projects',$projects);
		$this->view->set('opportunities',$opportunities);
		$this->view->set('activities',$activities);
	}
	
	public function edit() {
		parent::edit();
		$this->setTemplateName('calls_new');
	}

	public function index($collection = null, $sh = '', &$c_query = null){
/*
		$this->view->set('clickaction', 'view');
		parent::index(new LoggedCallCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'LoggedCalls','action'=>'new'),
					'tag'=>'new_call'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
*/
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('LoggedCall');
		sendBack();
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$call_data=&$this->_data['LoggedCall'];
		if(isset($call_data['start_time'])) {
			$call_data['start_time'].=' '.$call_data['start_time_hours'].':'.$call_data['start_time_minutes'];
		}
		if(isset($call_data['end_time'])) {
			$call_data['end_time'].=' '.$call_data['end_time_hours'].':'.$call_data['end_time_minutes'];
		}
		$flash=Flash::Instance();
		if(parent::save('LoggedCall')) {
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		} else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}

	public function view() {
		$this->_templateobject->setDefaultDisplayFields(array('subject'
										  ,'direction'
										  ,'company'
										  ,'person'
										  ,'start_time'
										  ,'end_time'
										  ,'owner'
										  ,'project'
										  ,'opportunity'
										  ,'activity'
										  ,'parent'
										  ,'ticket'
										  ,'notes'));
		$this->_templateobject->setDisplayFields();
		parent::view();
		
	}
	
}
?>