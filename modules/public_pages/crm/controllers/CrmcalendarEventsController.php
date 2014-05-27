<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CrmcalendareventsController extends printController {

	protected $version = '$Revision: 1.1 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
	
		parent::__construct($module, $action);
		
		$this->_templateobject = new CRMCalendarEvent();
		$this->uses($this->_templateobject);

	}

	public function index()
	{
		
		// when an item is clicked go the the view action
		$this->view->set('clickaction', 'view');
				
		parent::index(new CRMCalendarEventCollection($this->_templateobject));
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'view_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendars',
						'action'		=> 'index'
					),
					'tag' => 'View CRM Calendar'
				)
			)
		);
		
		$sidebar->addList(
			'Events',
			array(
				'new_event' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'new_event',
						'calendar_id'	=> $this->_data['id']
					),
					'tag' => 'New Event'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
				
	}
	
	public function view()
	{
		
		$flash = Flash::Instance();
		
		$event = $this->_templateobject;
		$event->load($this->_data['id']);
		
		if (!$event->loaded)
		{
			$flash->addError('Cannot view event');
			sendBack();
		}
		
		$this->view->set('event', $event);
		
		// sidebar
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'view_calendar' => array(
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendars'
					),
					'tag'=> 'View CRM Calendar'
				),
				'manage_calendars' => array(
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendars',
						'action'		=> 'view_calendars'
					),
					'tag'=> 'Manage Calendars'
				)
			)
		);
		
		$sidebar->addList(
			'Event',
			array(
				'edit_event' => array(
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'edit',
						'id'			=> $this->_data['id']
					),
					'tag'=> 'Edit Event'
				),	
				'delete_event' => array(
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'delete',
						'id'			=> $this->_data['id']
					),
					'tag'=> 'Delete Event'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	// new / edit event
	public function _new()
	{
	
		$event = $this->_templateobject;
		
		if ($this->_data['action'] == 'edit_event') { 
			$event->load($this->_data['id']);
		}
		
		$this->view->set('model', $event);
		
		if (isset($this->_data['calendar_id']))
		{
			$this->view->set('crm_calendar_id', $this->_data['calendar_id']);
		}
		
		// set sidebar
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'crm_calendar' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendars',
						'action'		=> 'index'
					),
					'tag' => 'CRM Calendar'
				),
				'manage_calendars' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'crmcalendars',
						'action'		=> 'view_calendars'
					),
					'tag' => 'Manage Calendars'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		parent::_new();
		
	}
	
	public function save()
	{
		
		$flash = Flash::Instance();
		
		$errors = array();
		$save = parent::save('CRMCalendarEvent', array(), $errors);
		
		if (is_ajax())
		{
			echo json_encode(array('status' => $save));
			exit;
		}
		else
		{
			sendBack();	
		}
			
	}
	
	public function delete()
	{
		
		$flash = Flash::instance();
		
		$event = $this->_templateobject;
		$event->load($this->_data['id']);
		
		if ($event->delete())
		{
			$flash->addMessage('Event deleted successfully');
			sendTo('crmcalendars', 'index', $this->_modules);
		}
		else
		{
			$flash->addError('Failed to delete event');
			sendBack();
		}
		
	}
	
	public function get_events()
	{
	
		$calendar	= $this->_templateobject;	
		$colours	= $calendar->getEnumOptions('colour');
						
		$crm_events = new CRMCalendarEventCollection();
		
		$sh = new SearchHandler($crm_events, FALSE);
		$sh->addConstraint(new Constraint('end_date', '>=', date('Y-m-d H:i:s', $this->_data['start'])));
		$sh->addConstraint(new Constraint('start_date', '<', date('Y-m-d H:i:s', $this->_data['end'])));
		
		$crm_events->load($sh);
		
		$output_events = array();
		
		// pardon my ignorance, but we shouldn't have to check is an array is empty... right?
		if (!empty($crm_events))
		{
		
			foreach ($crm_events as $event)
			{
				
				$output_events[] = array(
					'id'		=> $event->id,
					'title'		=> $event->title,
					'allDay'	=> TRUE,
					'start'		=> strtotime($event->start_date),	
					'end'		=> strtotime($event->end_date),
					'className'	=> 'fc_' . str_replace('#', '', $event->colour)
				);
				
			}
			
		}
		
		echo json_encode($output_events);
		exit;
		
	}
	
}

// end of CrmcalendarsController.php