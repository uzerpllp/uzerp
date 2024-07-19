<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HoursController extends Controller
{

	protected $_templateobject;
	protected $related;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Hour');
		
		$this->uses($this->_templateobject);
		
		$this->related['project'] = array('clickaction'=>'edit');
		
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors = array();
		
		$s_data = array();
		
		if (!empty($this->_data['person_id']))
		{
			$s_data['person_id'] = $this->_data['person_id'];
		}
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		else
		{
			$s_data['start_time']['from'] = date(DATE_FORMAT, strtotime("previous Monday"));
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		else
		{
			$s_data['start_time']['to'] = date(DATE_FORMAT, strtotime("next Monday")-1);
		}
		
		// Set context from calling module
		$this->setSearch('hoursSearch', 'useDefault', $s_data);
		
		$date_range = $this->search->getValue('start_time');
		
		if (!empty($date_range['from']))
		{
			$this->view->set('start_date', fix_date($date_range['from']));
		}
		if (!empty($date_range['to']))
		{
			$this->view->set('end_date', fix_date($date_range['to']));
		}
		
		$hours = new HourCollection();
		
		$sh = $this->setSearchHandler($hours);
		
		$fields = array('type_id', 'person', 'person_id', 'type');
		
		if (!isset($this->_data['page']) && !isset($this->_data['orderby']))
		{
			$sh->setOrderBy($fields);
		}
		
		$sh->setGroupBy($fields);
		
		$fields[] = 'sum(duration) as total_hours';
		
		$sh->setFields($fields);
		
		parent::index($hours, $sh);
		
		$this->view->set('fields', array('person', 'type', 'total_hours'));
		$this->view->set('page_title', 'Hours Summary');
		$this->view->set('clickcontroller', 'hours');
		$this->view->set('clickaction', 'view');
		$this->view->set('linkfield', 'person_id');
		$this->view->set('linkvaluefield', 'name');
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_Hours'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
			
		$this->setTemplateName('hours_index');
		
	}
	
	public function view(){

		$errors = array();
		
		$s_data = array();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		
		$search_fields = array('person_id', 'opportunity_id', 'project_id', 'task_id', 'ticket_id');
		
		foreach ($search_fields as $field)
		{
			if (isset($this->_data[$field]))
			{
				$s_data[$field] = $this->_data[$field];
			}
		}
		
		// Set context from calling module
		if ($this->module=='projects')
		{
			$options = array('opportunity', 'project', 'task');
			$this->_templateobject->setDefaultDisplayFields(array('person', 'start_time', 'duration', 'description', 'opportunity', 'project', 'task'));
		}
		elseif ($this->module=='tickets')
		{
			$options = array('ticket');
			$this->_templateobject->setDefaultDisplayFields(array('person', 'start_time', 'duration', 'description', 'ticket'));
		}
		else
		{
			$options = array('opportunity', 'project', 'task', 'ticket');
		}
		
		$this->setSearch('hoursSearch', 'useDefault', $s_data, $options);
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new HourCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new'),
					'tag'=>'new_Hours'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->setTemplateName('hours_index');
		
	}
	
	public function _new() {
	
		$this->new_edit();
		
		$this->view->set('equipment', 'false');

	}
	
	public function newequipment()
	{
		parent::_new();
		
		$this->view->set('equipment', 'true');
		
		$this->setTemplateName('hours_new');
	}
	
	public function edit()
	{
		if (!isset($this->_data) || !$this->loadData())
		{
// we are editing data, but either no id has been provided
// or the data for the supplied id does not exist
			$this->dataError();
			sendBack();
		}
		
		$this->new_edit();

	}
	
	public function delete($modelName = null)
	{
		$flash = Flash::Instance();
		
		parent::delete($this->modeltype);
		
		sendBack();
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		$hours_data = &$this->_data[$this->modeltype];
		
		if(isset($hours_data['start_time']))
		{
			$start_date_time = fix_date($hours_data['start_time']);
				
			$hours_data['start_time'] .= ' ' . $hours_data['start_time_hours'] . ':' . $hours_data['start_time_minutes'];

			$start_date_time .= ' ' . $hours_data['start_time_hours'] . ':' . $hours_data['start_time_minutes'];

		}
		
		if(isset($hours_data['duration']))
		{
			if($hours_data['duration_unit'] == 'days')
			{
				$hours_data['duration'] = $hours_data['duration'] * SystemCompanySettings::DAY_LENGTH * 60;
			}
			elseif($hours_data['duration_unit'] == 'hours')
			{
				$hours_data['duration'] = $hours_data['duration'] * 60;
			}
			
			$hours_data['duration'] .= ' minutes';
			
		}
		
		if(isset($hours_data['equipment']) && $hours_data['equipment'] === 'true')
		{
			$hours_data['equipment'] = true;
		}
		else
		{
			unset($hours_data['equipment']);
		}
		
		// Need to check for overlaps
		$hours_data['end_time'] = date(DATE_TIME_FORMAT, strtotime($start_date_time.'+'.$hours_data['duration']));
		
		$end_date_time = date(DB_DATE_TIME_FORMAT, strtotime($start_date_time.'+'.$hours_data['duration']));
		
		$db = DB::Instance();
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('start_time', 'between', $db->qstr($start_date_time) . ' and ' .  $db->qstr($end_date_time)));
		$cc->add(new Constraint('start_time+duration', 'between', $db->qstr($start_date_time) . ' and ' .  $db->qstr($end_date_time)), 'OR');
		
		$hour = DataObjectFactory::Factory('Hour');
		
		if ($hour->getCount($cc) > 0)
		{
			$errors[] = 'Date/time and duration overlaps existing entry';
		}
		
		if(count($errors)==0 && parent::save('Hour', $this->_data, $errors))
		{
			sendBack();
		} else {
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	public function view_my_hours()
	{
		
		$errors = array();
		$s_data = array();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		else
		{
			$s_data['start_time']['from'] = date(DATE_FORMAT, strtotime("previous Monday"));
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		else
		{
			$s_data['start_time']['to'] = date(DATE_FORMAT, strtotime("next Monday")-1);
		}
		
		$search_fields=array('opportunity_id', 'project_id', 'task_id', 'ticket_id');
		
		foreach ($search_fields as $field)
		{
			if (isset($this->_data[$field]))
			{
				$s_data[$field] = $this->_data[$field];
			}
		}
		
		$this->setSearch('hoursSearch', 'useMySearch', $s_data, array('opportunity', 'project', 'task', 'ticket'));
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new HourCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules,'controller'=>$this->name,'action'=>'new','owner'=>EGS_USERNAME),
					'tag'=>'new_Hours'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->setTemplateName('hours_index');
		
	}

	public function viewopportunity()
	{

		$search_fields = array('person_id', 'opportunity_id', 'project_id', 'task_id');
		$this->view_related_hours($search_fields);
		
	}
	
	public function viewproject()
	{

		$search_fields = array('person_id', 'opportunity_id', 'project_id', 'task_id');
		$this->view_related_hours($search_fields);
		
	}
	
	public function viewtask()
	{

		$search_fields = array('person_id', 'opportunity_id', 'project_id', 'task_id');
		$this->view_related_hours($search_fields);
		
	}
	
	public function viewticket()
	{

		$search_fields = array('person_id', 'ticket_id');
		$this->view_related_hours($search_fields);
		
	}
	

/* Ajax functions */
	public function getHours($_start_date='', $_end_date='', $_user='')
	{
// Used by Ajax to get a list of hours for a user between two dates

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['start_date'])) { $_start_date=fix_date($this->_data['start_date']); }
			if(!empty($this->_data['end_date'])) { $_end_date=$this->_data['end_date']; }
			if(!empty($this->_data['person_id'])) { $_user=$this->_data['person_id']; }
		}
		
		if(empty($_start_date)) { $_start_date=fix_date(date(DATE_FORMAT)); }
		
		if(empty($_end_date)) { $_end_date=fix_date(date(DATE_FORMAT, strtotime("+1 day", strtotime($_start_date)))); }
		
		if(empty($_user))
		{
			$user = getCurrentUser();
			if (!is_null($user->person_id)) { $_user=$user->person_id; }
		}

		$hours = new HourCollection();
		
		if (!empty($_user))
		{
			$sh = new SearchHandler($hours, false);
			
			$sh->addConstraint(new Constraint('person_id', '=', $_user));
			$sh->addConstraint(new Constraint('start_time', 'between', "'".$_start_date."' and '".$_end_date."'"));
			
			$sh->setFields(array('id', 'start_time', 'type', 'duration'));
			
			$sh->setOrderBy('start_time', 'ASC');
			
			$hours->load($sh);
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('no_ordering', true);
			$this->view->set('collection',$hours);
			$this->view->set('showheading',true);
			$this->setTemplateName('datatable_inline');
		}
		else
		{
			return $hours;
		}
		
	}
	
	public function getProjectList($_opportunity_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['opportunity_id'])) { $_opportunity_id=$this->_data['opportunity_id']; }
		}
		
		if (!empty($_opportunity_id))
		{
			$depends = array('opportunity_id'=>$_opportunity_id);
		}
		else
		{
			$depends = array();
		}
		
		$tasks = $this->getOptions($this->_templateobject, 'project_id', '', '', '', $depends);
		
		if(isset($this->_data['ajax']))
		{
			echo $tasks;
			exit;
		}
		else
		{
			return $tasks;
		}
		
	}
	
	public function getTaskList($_project_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['project_id'])) { $_project_id=$this->_data['project_id']; }
		}
		
		if (!empty($_project_id))
		{
			$depends = array('project_id'=>$_project_id);
		} else {
			$depends = array();
		}
		
		$tasks = $this->getOptions($this->_templateobject, 'task_id', '', '', '', $depends);
		
		if(isset($this->_data['ajax']))
		{
			echo $tasks;
			exit;
		}
		else
		{
			return $tasks;
		}
		
	}
	

// Protected Functions
	
// Private functions
	private function new_edit ()
	{
		
		parent::_new();
		
		$hour = $this->_uses[$this->modeltype];
		
		if ($hour->isLoaded())
		{
			$this->_data['person']		= $hour->person;
			$this->_data['person_id']	= $hour->person_id;
			
			$hours = $this->getHours(fix_date(un_fix_date($hour->start_time)), '', $hour->person_id);
		}
		else
		{
			$hours = $this->getHours();
		}

		$this->_templateName = $this->getTemplateName('hours_new');
		
		if (isset($this->_data['opportunity_id']))
		{
			$this->view->set('page_title', $this->getPageName('Opportnity Hours'));
		}
		elseif (isset($this->_data['project_id']))
		{
			$this->view->set('page_title', $this->getPageName('Project Hours'));
		}
		elseif (isset($this->_data['task_id']))
		{
			$this->view->set('page_title', $this->getPageName('Task Hours'));
		}
		elseif (isset($this->_data['ticket_id']))
		{
			$this->view->set('page_title', $this->getPageName('Ticket Hours'));
		}

		if (isset($this->_data['person']))
		{
			$this->view->set('page_title', $this->getPageName().' for '.$this->_data['person']);
		}
		elseif (isset($this->_data['company_id']))
		{
			$person = DataObjectFactory::Factory('person');
			$this->view->set('people', $person->getByCompany());
		}
		
		if (!$hour->isLoaded() && !empty($this->_data['project_id']))
		{
			$hour->project_id = $this->_data['project_id'];
		}
		
		$this->view->set('tasks', $this->getTaskList($hour->project_id));
		
		$this->view->set('no_ordering', true);
		$this->view->set('collection',$hours);
		$this->view->set('showheading',true);
	
	}
	
	private function view_related_hours($search_fields = array())
	{
		
		$errors = array();
		$s_data = array();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		else
		{
			$s_data['start_time']['from'] = date(DATE_FORMAT, strtotime("previous Monday"));
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		else
		{
			$s_data['start_time']['to'] = date(DATE_FORMAT, strtotime("next Monday")-1);
		}
		
		foreach ($search_fields as $field)
		{
			if (isset($this->_data[$field]))
			{
				$s_data[$field] = $this->_data[$field];
			}
		}
		
		// Set context from calling module
		$this->setSearch('hoursSearch', 'useDefault', $s_data);
		
		$this->view->set('clickaction', 'edit');
		
		$hour = DataObjectFactory::Factory('Hour');
		
		$hour->setDefaultDisplayFields(array('person', 'start_time', 'duration', 'description'));
		
		parent::index(new HourCollection($hour));
		
		$this->setTemplateName('hours_index');
		
	}
	
}

// End of HoursController
