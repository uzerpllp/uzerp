<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class IndexController extends Controller {
	
	protected $version='$Revision: 1.15 $';
	
	protected $_templateobject;
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Calendar();
		$this->uses($this->_templateobject);
	}
	
	function index() {
		

		
		$userPreferences = UserPreferences::instance();
		$view = $userPreferences->getPreferenceValue('default-calendar-view','calendar');
	
		$display_options=unserialize($userPreferences->getPreferenceValue('display-calendar-filter','calendar'));
		$calendars=array();
		$calendars=$this->getCalendarList($display_options);

		$this->view->set('default_view',$view);
		$this->view->set('calendars',$calendars);
		
		
		$this->view->set('writable_calendars',$this->getWriteableCalendars());
		
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
					'tag'=>'view_agenda'
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
		
	}	
	
	function change_calendar() {
		
		$userPreferences = UserPreferences::instance();
		$calendars=unserialize($userPreferences->getPreferenceValue('display-calendar-filter','calendar'));

		$data=split("_",$this->_data['change_cal']);

		if($data[1]=="true") {
			$calendars[$data[0]]['status']='on';
		} else {
			$calendars[$data[0]]['status']='off';
		}
		
		$userPreferences->setPreferenceValue('display-calendar-filter','calendar',serialize($calendars));
	
	}
		
	function getJSON() {
	
		$cal_events=new CalendarEventCollection(new CalendarEvent);
		$sh = new SearchHandler($cal_events, false);
		$sh->addConstraint(new Constraint('end_time', '>=', date('o-m-d H:i:s', $this->_data['start'])));
		$sh->addConstraint(new Constraint('start_time', '<', date('o-m-d H:i:s', $this->_data['end'])));
		$sh->addConstraint(new Constraint('calendar_id', '=', $this->_data['id']));
		$cal_events->load($sh);
		
		$calendar=$this->_uses['Calendar'];
		$editable=$calendar->isWritable($this->_data['id']);
		
		$events=array();
		
		foreach ($cal_events as $key=>$value) {
			$events[]=array('id'=>$value->id,
							'title'=>($value->private=='t' && $value->owner != EGS_USERNAME) ? 'Private' : $value->title,
							'allDay'=>($value->all_day=='t') ? true : false,
							'start'=>strtotime($value->start_time),	
							'end'=>strtotime($value->end_time),
							'editable'=>$editable,
							'url'=>($value->private=='t' && $value->owner != EGS_USERNAME) ? '' : '/?module=calendar&controller=calendarevents&action=view&id='.$value->id,	
							'className'=>'fc_'.str_replace("#","",$value->colour).' fc_'.$value->calendar_id
					);
		}
		
		echo json_encode($events);
		exit;
		
	}
	
	function save_display() {
		// owners
		$owner=new CalendarCollection(new Calendar);
		$sh = new SearchHandler($owner,false);;
		$owner->load($sh);
		foreach($owner as $key=>$value) {
			if(isset($this->_data['calendar'][$value->id])) {
				$owners[$value->id]['status']='on';
			} else {
				$owners[$value->id]['status']='off';
			}
		}
		$userPreferences = UserPreferences::instance();
		$userPreferences->setPreferenceValue('display-calendar-filter','calendar',serialize($owners));
		sendTo('index',$this->_data['referrer_view'],array('calendar'));
	}
	
	function getCalendarList($options=Array()) {
		
		$calendars=new CalendarCollection(new Calendar);
		$sh = new SearchHandler($calendars, false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('owner', '=', EGS_USERNAME));
		$cc->add(new Constraint('username', '=', EGS_USERNAME),'OR');
		$sh->addConstraintChain($cc);
		$sh->setOrderby('name','ASC');
		$calendars->load($sh);
		$calendar_list=$calendars->getArray();
				
		if(count($calendar_list)>0) {
			foreach($calendar_list as $key=>$value) {
				if(isset($options[$value['id']]['status']) && $options[$value['id']]['status']=='on') {
					$calendar_list[$key]['show']=true;
				} else {
					$calendar_list[$key]['show']=false;
				}
				switch($value['type']) {
					case "personal":
					case "group":
						$calendar_list[$key]['url']="/?module=calendar&controller=index&action=getJSON&id=".$value['id'];
						break;	
					case "gcal":
						$calendar_list[$key]['url']=$calendar_list[$key]['gcal_url'];
						break;	
				}
				
				
				$calendar_list[$key]['className']=str_replace("#","",$calendar_list[$key]['colour']);
				
			}
		}
		return $calendar_list;
	}
	
	// ATTENTION, is this actually getting the calendars properly?!
	// fixed (read: bodged), check this SQL
	function getWriteableCalendars() {
		
		$calendar=$this->_uses['Calendar'];
		return $calendar->getWritableCalendars();
		/*$calendars=new CalendarCollection(new Calendar);
		$sh = new SearchHandler($calendars, false);
		
		$sh->addConstraint(new Constraint('type', '!=', 'gcal'));
		$sh->addConstraint(new Constraint('owner', '=', EGS_USERNAME));
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('username', '=', EGS_USERNAME));
		$cc->add(new Constraint('type', '=', 'group'),'AND');
		$sh->addConstraintChain($cc,'OR');

		$sh->setOrderby('name','ASC');
		$calendars->load($sh);
		
		$list=Array();
		
		foreach($calendars->getArray() as $key=>$value) {
			switch($value['type']) {
				case "personal":
				case "group":
					$list[$value['id']]=$value['name'];
					break;
			}
		}
		
		return $list;*/
	}
	
	function getCalendars() {
		$userPreferences = UserPreferences::instance();
		$view = $userPreferences->getPreferenceValue('default-calendar-view','calendar');
	
		$display_options=unserialize($userPreferences->getPreferenceValue('display-calendar-filter','calendar'));
		$calendars=array();
		$calendars=$this->getCalendarList($display_options);
	
		echo json_encode($calendars);
		exit;
	}
}

?>