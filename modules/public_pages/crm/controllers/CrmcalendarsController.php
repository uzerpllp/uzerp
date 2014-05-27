<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CrmcalendarsController extends printController {

	protected $version = '$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
	
		parent::__construct($module, $action);
		
		$this->_templateobject = new CRMCalendar();
		$this->uses($this->_templateobject);

	}
	
	// calendar view, not calendar index
	public function index()
	{
		
		// build a list of calendars for the sidebar
		$calendars = new CRMCalendarCollection($this->_templateobject);
		$sh = new SearchHandler($calendars, FALSE);
		$sh->setOrderby('title', 'ASC');
		$calendars->load($sh);
		
		$output_calendars = array();
		
		foreach ($calendars as $calendar)
		{
		
			$output_calendars[$calendar->id] = array(
				'title'	=> $calendar->title,
				'class'	=> str_replace("#", "", $calendar->colour)
			);
			
		}
		
		$this->view->set('calendars', $output_calendars);
		$this->view->set('dialog_calendars', $this->get_editable_calendars());
		
		// sidebar
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'manage_calendars' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'view_calendars'
					),
					'tag' => 'Manage Calendars'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		parent::index(new CRMCalendarCollection($this->_templateobject));
				
	}
	
	public function view_calendars()
	{
		
		// when an item is clicked go the the view action
		$this->view->set('clickaction', 'view');
				
		parent::index(new CRMCalendarCollection($this->_templateobject));
		
		// parent::index is going to set the template to index, we need it to be view_calendars
		$this->_templateName = $this->getTemplateName('view_calendars');
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'view_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'index'
					),
					'tag' => 'View CRM Calendar'
				)
			)
		);
		
		$sidebar->addList(
			'Calendar',
			array(
				'new_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'new'
					),
					'tag' => 'New Calendar'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	// view calendar
	public function view()
	{
		
		$calendar = $this->_templateobject;
		$calendar->load($this->_data['id']);
		
		$this->view->set('model', $calendar);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'crm_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name
					),
					'tag' => 'CRM Calendar'
				),
				'manage_calendars' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'view_calendars'
					),
					'tag' => 'Manage Calendars'
				)
			)
		);
		
		$sidebar->addList(
			'Calendar',
			array(
				'edit_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'edit',
						'id'			=> $this->_data['id']
					),
					'tag' => 'Edit Calendar'
				)
			)
		);
		
		$sidebar->addList(
			'Events',
			array(
				'new_event' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendarevents',
						'action'		=> 'new',
						'calendar_id'	=> $this->_data['id']
					),
					'tag' => 'New Event'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
	
	}
	
	// new / edit calendar
	public function _new()
	{
	
		$calendar = $this->_templateobject;
		
		$colours = $calendar->getEnumOptions('colour');
		$this->view->set('colours', $colours);
		
		if ($this->_data['action'] == 'edit')
		{ 
			$calendar->load($this->_data['id']);
		}
		
		$this->view->set('model', $calendar);
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'crm_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name
					),
					'tag' => 'CRM Calendar'
				),
				'view_calendars' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'view_calendars'
					),
					'tag' => 'Manage Calendars'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	// save calendar
	public function save()
	{
	
		$flash	= Flash::Instance();
		$errors	= array();
		
		$calendar = $this->_templateobject;

		if (!isset($this->_data['CRMCalendar']['colour']))
		{
			$colours = $calendar->getEnumOptions('colour');
			$this->_data['CRMCalendar']['colour'] = $colours[array_rand($colours)];
		}
	
		if (parent::save('CRMCalendar', '', $errors))
		{
			sendBack();
		}
		else
		{
			sendBack();
		}
		
	}
	
	public function get_editable_calendars()
	{
		
		$output_calendars = array();
	
		// build a list of calendars for the sidebar
		$calendars = new CRMCalendarCollection($this->_templateobject);
		
		$sh = new SearchHandler($calendars, FALSE);
		$sh->setOrderby('title', 'ASC');
		
		$calendars->load($sh);
		
		foreach ($calendars as $calendar)
		{
			$output_calendars[$calendar->id] = $calendar->title;	
		}
	
		return $output_calendars;
	
	}

}

// end of CrmcalendarsController.php