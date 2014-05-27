<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CalendareventsController extends printController {

	protected $version='$Revision: 1.13 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CalendarEvent();
		$this->uses(new CalendarEventAttendee());
		$this->uses($this->_templateobject);
	}

	public function _new() {
		$calendar=new Calendar();
		$this->view->set('calendar_id',$calendar->getWritableCalendars());
		parent::_new();
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view_events'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'index'),
					'tag'=>'view_events'
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
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function edit() {
		$flash = Flash::Instance();
		$ce = $this->_uses['CalendarEvent'];
		$ce->load($this->_data['id']);
		$calendar=new Calendar();
		$this->view->set('calendar_id',$calendar->getWritableCalendars());
		if ($ce->owner != EGS_USERNAME) {
			$flash->addError('You do not have permission to edit that entry');
			sendBack();
		}
		parent::edit();
	}

	public function getallids() {
		$db = DB::Instance();
		$query = 'select id from calendar_events where owner='.$db->qstr(EGS_USERNAME).' limit 1';
		echo json_encode($db->GetAll($query));
		exit;
	}

	public function getinformationbyid() {
		$ce = new CalendarEvent;
		$ce->load($this->_data['id']);
		$data = array();
		$data['start_time'] = $ce->start_time;
		$data['end_time'] = $ce->end_time;
		$data['all_day'] = $ce->all_day;
		$data['summary'] = $ce->summary;
		$data['description'] = $ce->description;
		$data['location'] = $ce->location;
		$data['url'] = $ce->url;
		$data['status'] = $ce->status;
		$data['private'] = $ce->private;
		echo json_encode($data);
		exit;
	}

	public function view() {
		$flash=Flash::Instance();
		
		$event = $this->_uses['CalendarEvent'];
		$event->load($this->_data['id']);

		if($event->private=='t' && $event->owner != EGS_USERNAME) {
			$flash->addError("Cannot view a private event");
			sendBack();
		}
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new_event'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendarevents','action'=>'new'),
					'tag'=>'new_event'
				)
			)
		);
		
		if ($event->owner == EGS_USERNAME) {
			$sidebar->addCurrentBox('currently_viewing',$event->summary,array('module'=>'calendar','controller'=>'calendarevents','action'=>'edit','id'=>$event->id));
		}
		$sidebar->addList(
			'calendar_views',
			array(
				'new_event'=>array(
					'link'=>array('module'=>'calendar'),
					'tag'=>'View Calendar'
				)
			)
		);
		$sidebar->addList(
			'related_items',
			array(
				'attachments'=>array(
					'tag'=>'Attachments',
					'link'=>array('module'=>'calendar','controller'=>'calendareventattachments','action'=>'index','entity_id'=>$event->id),
					'new'=>array('module'=>'calendar','controller'=>'calendareventattachments','action'=>'new','entity_id'=>$event->id)
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);		
	}

	public function index(){
		$this->view->set('clickaction', 'view');
	
		$s_data=array();
		$s_data['owner']=EGS_USERNAME;

		$this->setSearch('CalendarSearch', 'useDefault', $s_data);
				
		parent::index(new CalendarEventCollection($this->_templateobject));
		
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
				'view_calendars'=>array(
					'link'=>array('module'=>'calendar','controller'=>'calendars','action'=>'index'),
					'tag'=>'view_calendars'
				),
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

	public function delete() {
		$flash = Flash::Instance();
		$ce = $this->_uses['CalendarEvent'];
		$ce->load($this->_data['id']);
		if ($ce->owner != EGS_USERNAME) {
			$csc = new CalendarShareCollection(new CalendarShare());
			$sh = new SearchHandler($csc);
			$sh->addConstraint(new Constraint('username','=',EGS_USERNAME));
			$csc->load($sh);
			if ($csc->count() == 0) {
				$flash->addError('You do not have permission to edit that entry');
				sendBack();
			}
		}
		parent::delete('CalendarEvent');
		sendTo('index','index',array('calendar'));
	}

	public function check_collisions($start_time, $end_time) {
		if ($start_time == ' 00:00' || $end_time == ' 00:00')
			return false;
		list($start_date, $start_time) = explode(' ',$start_time);
		list($end_date, $end_time) = explode(' ',$end_time);
		list($start_day, $start_month, $start_year) = explode('/',$start_date);
		list($end_day, $end_month, $end_year) = explode('/',$end_date);
		$start_time = strtotime($start_month.'/'.$start_day.'/'.$start_year.' '.$start_time.':00');
		$end_time = strtotime($end_month.'/'.$end_day.'/'.$end_year.' '.$end_time.':00');
		$db = DB::Instance();
		$query = 'SELECT id, start_time, end_time FROM calendar_events WHERE owner='.$db->qstr(EGS_USERNAME);
		$busy_dates = $db->GetAssoc($query);
		foreach ($busy_dates as $id=>$ts) {
			$st = strtotime($ts['start_time']);
			$et = strtotime($ts['end_time']);
			if ((($start_time > $st) && ($start_time < $et)) || (($end_time > $st) && ($end_time < $et)))
				return true;
		}
		return false;
	}

	public function confirm_collision() {
		$cal_fields = $this->_data['CalendarEvent'];
		$this->view->set('cal_fields',$cal_fields);
	}
	
	public function save() {
		
		/**
		 * to ensure the redirect htis the right page, we must check the 
		 * original action if it is day, week or month view we can construct 
		 * a sentTo, if it is anything else we must fire a sendBack
		 */
		switch($this->_data['original_action']) {
			case "dayview":
			case "weekview":
			case "monthview":
				$original_action=$this->_data['original_action'];
				break;
			default:
				$original_action=FALSE;
		}
			
		$flash=Flash::Instance();
		$calendar=new Calendar();
		if(!isset($this->_data['CalendarEvent']['calendar_id']) || $this->_data['CalendarEvent']['calendar_id']=='') {
			$flash->addError("You must specify a calendar");
			if($original_action!=FALSE) {
				sendTo('index',$original_action,'calendar');
			} else {
				sendBack(); 
			}
		}
		if(!array_key_exists($this->_data['CalendarEvent']['calendar_id'],$calendar->getWritableCalendars())) {
			$flash->addError("You haven't got permission to write to this calendar");
			if($original_action!=FALSE) {
				sendTo('index',$original_action,'calendar');
			} else {
				sendBack(); 
			}
		}
		if (isset($this->_data['CalendarEventAttendee'])) {
			$attendees = DataObjectCollection::joinArray($this->_data['CalendarEventAttendee'],1);
			foreach ($attendees as $key=>$attendee) 
				foreach ($attendees as $s_key=>$s_attendee)
					if ($attendee['person_id'] == $s_attendee['person_id'] && $key <> $s_key) {
						$this->_new();
						$this->_templateName=$this->getTemplateName('new');
						$flash->addError('Please ensure to only add any attendee once');
						return;
					}
		}
		if (!isset($this->_data['book'])) {
			if($this->_data['CalendarEvent']['start_time']<>$this->_data['CalendarEvent']['end_time']) {
				$this->_data['CalendarEvent']['all_day']=true;
			} 
			if(isset($this->_data['CalendarEvent']['start_time'])) {
				$this->_data['CalendarEvent']['start_time'].=(!empty($this->_data['CalendarEvent']['start_time_hours'])?' '.$this->_data['CalendarEvent']['start_time_hours']:' 00');
				$this->_data['CalendarEvent']['start_time'].=':';
				$this->_data['CalendarEvent']['start_time'].=(!empty($this->_data['CalendarEvent']['start_time_minutes'])?$this->_data['CalendarEvent']['start_time_minutes']:'00');
			}
			if(isset($this->_data['CalendarEvent']['end_time'])) {
				$this->_data['CalendarEvent']['end_time'].=(!empty($this->_data['CalendarEvent']['end_time_hours'])?' '.$this->_data['CalendarEvent']['end_time_hours']:' 00');
				$this->_data['CalendarEvent']['end_time'].=':';
				$this->_data['CalendarEvent']['end_time'].=(!empty($this->_data['CalendarEvent']['end_time_minutes'])?$this->_data['CalendarEvent']['end_time_minutes']:'00');
			}
			$temp_start_time=$this->_data['CalendarEvent']['start_time_hours'].":".$this->_data['CalendarEvent']['start_time_minutes'];
			$temp_end_time=$this->_data['CalendarEvent']['end_time_hours'].":".$this->_data['CalendarEvent']['end_time_minutes'];
			if($temp_start_time=='00:00' || $temp_start_time==':' ||
			   $temp_end_time=='00:00' || $temp_end_time==':') {
				$this->_data['CalendarEvent']['all_day']=true;
			} 
			if ($this->check_collisions($this->_data['CalendarEvent']['start_time'],$this->_data['CalendarEvent']['end_time']) !== false) {
				$this->confirm_collision();
				$this->_templateName=$this->getTemplateName('confirm_collision');
				return;			
			}
		} elseif (isset($this_data['dont_book'])) {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
			return;			
		}
		
		if(parent::save('CalendarEvent')) {
			if (isset($this->_data['CalendarEventAttendee'])) {
				foreach ($attendees as $attendee) {
					$attendee['calendar_event_id'] = $this->_data['id'];
					$model= call_user_func(array('CalendarEventAttendee', "Factory"),$attendee,array(),'CalendarEventAttendee');
					if(is_a($model, 'CalendarEventAttendee')) {
						$model->save();
					}
				}
			}
			if($original_action!=FALSE) {
				sendTo('index',$original_action,'calendar');
			} else {
				sendBack(); 
			}
		}
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
	
	/* ajax add / edit / update events */
	
	public function new_ajax_event() {
		
		$calendar = new Calendar();
		if(!$calendar->isWritable($this->_data['CalendarEvent']['calendar_id'])) {
			json_reply(array('success' => FALSE));
		}
	
		if($this->_data['CalendarEvent']['start_time']<>$this->_data['CalendarEvent']['end_time']) {
			$this->_data['CalendarEvent']['all_day']=true;
		} 
		if(isset($this->_data['CalendarEvent']['start_time'])) {
			$this->_data['CalendarEvent']['start_time'].=(!empty($this->_data['CalendarEvent']['start_time_hours'])?' '.$this->_data['CalendarEvent']['start_time_hours']:' 00');
			$this->_data['CalendarEvent']['start_time'].=':';
			$this->_data['CalendarEvent']['start_time'].=(!empty($this->_data['CalendarEvent']['start_time_minutes'])?$this->_data['CalendarEvent']['start_time_minutes']:'00');
		}
		if(isset($this->_data['CalendarEvent']['end_time'])) {
			$this->_data['CalendarEvent']['end_time'].=(!empty($this->_data['CalendarEvent']['end_time_hours'])?' '.$this->_data['CalendarEvent']['end_time_hours']:' 00');
			$this->_data['CalendarEvent']['end_time'].=':';
			$this->_data['CalendarEvent']['end_time'].=(!empty($this->_data['CalendarEvent']['end_time_minutes'])?$this->_data['CalendarEvent']['end_time_minutes']:'00');
		}
		$temp_start_time=$this->_data['CalendarEvent']['start_time_hours'].":".$this->_data['CalendarEvent']['start_time_minutes'];
		$temp_end_time=$this->_data['CalendarEvent']['end_time_hours'].":".$this->_data['CalendarEvent']['end_time_minutes'];
		if($temp_start_time=='00:00' || $temp_start_time==':' ||
		   $temp_end_time=='00:00' || $temp_end_time==':') {
			$this->_data['CalendarEvent']['all_day']=true;
		}
		
		$error=array();
		
		if(parent::save('CalendarEvent','',$error)) {
			json_reply(array('success' => TRUE));
		} else {
			json_reply(array('success' => FALSE));
		}
		
	}
	
	public function updateEvent() {
		// check if user is owner
		$event = $this->_uses['CalendarEvent'];
		$event->load($this->_data['id']);
		
		$data=$event->_data;
		
		// I'm sure there's a better way to format these dates... 
		switch($this->_data['type']) {
			case "drop":
				$data['start_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['start_time'])))),true);
				$data['end_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['end_time'])))),true);
				$data['all_day']=$this->_data['allDay'];
				break;
			case "resize":
				$data['start_time']=un_fix_date($data['start_time'],true);
				$data['end_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['end_time'])))),true);
				break;
		}
		
		$errors=array();
		
		if(parent::save('CalendarEvent',$data,$errors)) {
			json_reply(array('success' => TRUE));
		} else {
			json_reply(array('success' => FALSE));
		}
		
	}
	
}
	
?>