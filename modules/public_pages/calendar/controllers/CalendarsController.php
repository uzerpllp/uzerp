<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CalendarsController extends printController {
	
	protected $version='$Revision: 1.6 $';
	
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Calendar();
		$this->uses($this->_templateobject);
	}

	function index() {
		$this->view->set('clickaction', 'view');
	
		$s_data=array();
		$s_data['owner']=EGS_USERNAME;

		$this->setSearch('CalendarSearch', 'useDefault', $s_data);
				
		parent::index(new CalendarCollection($this->_templateobject));
		
		$calendar = new CalendarCollection($this->_templateobject);
		$sh = new SearchHandler($calendar,false);
		$sh->addConstraint(new Constraint('owner', '=', EGS_USERNAME));
		$sh->setOrderby('name','ASC');
		$calendar->load($sh);
		
		if (isset($this->search) && ($this->isPrintDialog() || $this->isPrinting()) ) {
			$this->printCollection($calendar);
		}
		
		$this->view->set('calendar',$calendar);
		$this->view->set('num_records',$calendar->num_records);		

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Events',
			array(
				'new_event'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'new'),
					'tag'=>'new_event'
				),
				'view_events'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'index'),
					'tag'=>'view_events'
				)
			)
		);
		$sidebar->addList(
			'Tasks',
			array(
				'new_task'=>array(
					'link'=>array('module'=>'projects','controller'=>'tasks','action'=>'new'),
					'tag'=>'new_task'
				),
				'view_tasks'=>array(
					'link'=>array('module'=>'projects','controller'=>'tasks','action'=>'index'),
					'tag'=>'view_tasks'
				)
			)
		);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'new_personal_calendar'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'new_personal'),
					'tag'=>'new_personal_calendar'
				),
				'new_group_calendar'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'new_group'),
					'tag'=>'new_group_calendar'
				),
				'add_google_calendar'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'new_gcal'),
					'tag'=>'add_google_calendar'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	function save() {
		$flash=Flash::Instance();
		$calendar=new Calendar();

		if(isset($this->_data['Calendar']['id']) && !$calendar->isOwner($this->_data['Calendar']['id'])) {
			$flash->addError("You cannot save a calendar that belongs to someone else");
			sendTo('calendars','index','calendar');
		}
		
		// if we dont specify any shared users, pass an empty array
		if(isset($this->_data['CalendarShareCollection'])) {
			if (!$this->checkParams(array('Calendar','CalendarShareCollection'))) {
				sendBack();
			}
		}
		
		if($this->_data['Calendar']['type']=='gcal' && (!isset($this->_data['Calendar']['gcal_url']) || empty($this->_data['Calendar']['gcal_url']))) {
			$flash->addError("You haven't specified a feed URL");
			sendBack();
		}
		
		if(!isset($this->_data['Calendar']['colour'])) {
			$colours=$calendar->getEnumOptions('colour');
			$this->_data['Calendar']['colour']=$colours[array_rand($colours)];
		}
		
		if(isset($this->_data['Calendar']['id'])) {
			$calendarshare=new CalendarShareCollection(new CalendarShare);
			$sh=new SearchHandler($calendarshare, false);
			$sh->addConstraint(new Constraint('calendar_id', '=', $this->_data['Calendar']['id']));
			$calendarshare->delete($sh);
		}
	
		// apply calendar_id to CalendarShareCollection
		if (isset($this->_data['CalendarShareCollection']) && !empty($this->_data['CalendarShareCollection']['username'])) {
			foreach ($this->_data['CalendarShareCollection']['username'] as $key=>$value) {
				$this->_data['CalendarShareCollection']['calendar_id'][$key]='';
			}
		}
		
		$errors=array();
		if(parent::save('Calendar','',$errors)) {
			sendTo('index','index','calendar');
		} else {
			sendBack();
		}
	}
	
	function new_personal() {
		$calendar=new Calendar();
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
		
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);
		
		$shared_user=array(); // we know we won't have any shared users for a new event
		$this->view->set('shared_users',$shared_user);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();
	}
	
	function new_group() {
		$calendar=new Calendar();
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
		
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);
		
		$shared_user=array(); // we know we won't have any shared users for a new event
		$this->view->set('shared_users',$shared_user);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();
	}
		
	function new_gcal() {
		$calendar=new Calendar();
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
		
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);
		
		$shared_user=array(); // we know we won't have any shared users for a new event
		$this->view->set('shared_users',$shared_user);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();
	}

	function edit_personal() {
		$flash=Flash::Instance();
		$calendar=new Calendar();
		$calendar->load($this->_data['id']);
		$this->view->set('calendar',$calendar);
		
		if(!$calendar->isOwner($this->_data['id'])) {
			$flash->addError("You cannot edit a calendar that doesn't belong to you");
			sendTo('calendars','index','calendar');
		}
		
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
				
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);

		$shared_users=array();
		$shared_user = new CalendarShareCollection(new CalendarShare);
		$sh=new SearchHandler($shared_user,false);
		$sh->addConstraint(new Constraint('calendar_id', '=', $this->_data['id']));	
		$sh->addConstraint(new Constraint('username', '!=', EGS_USERNAME));	
		$sh->setFields(array('id', 'calendar_id','username'));	
		$shared_user->load($sh);
		if(count($shared_user->getArray())>0) {
			foreach($shared_user->getArray() as $key=>$value) {
				$shared_users[$key]=$value['username'];
			}
		}
		$this->view->set('shared_users',$shared_users);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();	
	}
	
	function edit_group() {
		$flash=Flash::Instance();
		$calendar=new Calendar();
		$calendar->load($this->_data['id']);
		$this->view->set('calendar',$calendar);
		
		if(!$calendar->isOwner($this->_data['id'])) {
			$flash->addError("You cannot edit a calendar that doesn't belong to you");
			sendTo('calendars','index','calendar');
		}
		
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
				
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);

		$shared_users=array();
		$shared_user = new CalendarShareCollection(new CalendarShare);
		$sh=new SearchHandler($shared_user,false);
		$sh->addConstraint(new Constraint('calendar_id', '=', $this->_data['id']));	
		$sh->addConstraint(new Constraint('username', '!=', EGS_USERNAME));	
		$sh->setFields(array('id', 'calendar_id','username'));	
		$shared_user->load($sh);
		if(count($shared_user->getArray())>0) {
			foreach($shared_user->getArray() as $key=>$value) {
				$shared_users[$key]=$value['username'];
			}
		}
		$this->view->set('shared_users',$shared_users);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();	
	}
		
	function edit_gcal() {
		$flash=Flash::Instance();
		$calendar=new Calendar();
		$calendar->load($this->_data['id']);
		$this->view->set('calendar',$calendar);
		
		if(!$calendar->isOwner($this->_data['id'])) {
			$flash->addError("You cannot edit a calendar that doesn't belong to you");
			sendTo('calendars','index','calendar');
		}
		
		$colours=$calendar->getEnumOptions('colour');
		$this->view->set('colours',$colours);
				
		$user = new User();
		$users=$user->getAll();
		foreach($users as $key=>$value) {
			if($value!=EGS_USERNAME) {
				$usernames[$key]=$value;
			}
		}
		$this->view->set('users',$usernames);

		$shared_users=array();
		$shared_user = new CalendarShareCollection(new CalendarShare);
		$sh=new SearchHandler($shared_user,false);
		$sh->addConstraint(new Constraint('calendar_id', '=', $this->_data['id']));	
		$sh->addConstraint(new Constraint('username', '!=', EGS_USERNAME));	
		$sh->setFields(array('id', 'calendar_id','username'));	
		$shared_user->load($sh);
		if(count($shared_user->getArray())>0) {
			foreach($shared_user->getArray() as $key=>$value) {
				$shared_users[$key]=$value['username'];
			}
		}
		$this->view->set('shared_users',$shared_users);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'calendar_views',
			array(
				'view_calendar'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'calendars',
			array(
				'manage_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'Manage Calendars'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		parent::_new();	
	}
	
}

?>