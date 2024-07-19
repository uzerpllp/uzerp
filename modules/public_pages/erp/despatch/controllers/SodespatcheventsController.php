<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SodespatcheventsController extends printController {

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new SODespatchEvent();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null) {
		$status_enums=$this->_templateobject->getEnumOptions('status');
		$this->view->set('status_enums',$status_enums);
		$legend=array($status_enums['TC']=>'fc_red',
					  $status_enums['TNC']=>'fc_green',
					  $status_enums['NBI']=>'fc_pink',
					  $status_enums['LED']=>'fc_yellow'
		);
		$this->view->set('legend',$legend);
		
		$sidebar = new SidebarController($this->view);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	public function view() {
		$event = $this->_uses['SODespatchEvent'];
		$event->load($this->_data['id']);
		$this->view->set('model',$event);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'view_all'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name),
					'tag'=>'View All'
				),
				'edit'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$this->_data['id']),
					'tag'=>'Edit Event'
				),
				'delete'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$this->_data['id']),
					'tag'=>'Delete Event'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new() {
		$event = $this->_uses['SODespatchEvent'];
		if ($this->_data['action']=='edit') { 
			$event->load($this->_data['id']);
		}
		$this->view->set('model',$event);
	}
	
	public function delete($modelName = null) {
		$flash = Flash::Instance();
		$accessobject=AccessObject::Instance();
		$editable=$accessobject->hasPermission('despatch','sodespatchevents','edit');
		
		if($editable) {
			$flash=Flash::instance();
			$event = $this->_uses['SODespatchEvent'];
			$event->load($this->_data['id']);
			$this->_data['SODespatchEvent']['id']=$this->_data['id'];
			$this->_data['SODespatchEvent']['status']='X';
			
			if(parent::save('SODespatchEvent')) {
				$flash->clearMessages();
				$flash->addMessage("Event successfully deleted");
				sendTo('sodespatchevents','index',array('despatch'));
			} else {
				$flash->addError("Failed to delete event");
				sendBack();
			}
		} else {
			$flash->addError("You don't have permission to delete an event");
			sendBack();
		}
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		
		if(isset($this->_data['SODespatchEvent']['start_time'])) {
			$this->_data['SODespatchEvent']['start_time'].=(!empty($this->_data['SODespatchEvent']['start_time_hours'])?' '.$this->_data['SODespatchEvent']['start_time_hours']:' 00');
			$this->_data['SODespatchEvent']['start_time'].=':';
			$this->_data['SODespatchEvent']['start_time'].=(!empty($this->_data['SODespatchEvent']['start_time_minutes'])?$this->_data['SODespatchEvent']['start_time_minutes']:'00');
		}
		if(isset($this->_data['SODespatchEvent']['end_time'])) {
			$this->_data['SODespatchEvent']['end_time'].=(!empty($this->_data['SODespatchEvent']['end_time_hours'])?' '.$this->_data['SODespatchEvent']['end_time_hours']:' 00');
			$this->_data['SODespatchEvent']['end_time'].=':';
			$this->_data['SODespatchEvent']['end_time'].=(!empty($this->_data['SODespatchEvent']['end_time_minutes'])?$this->_data['SODespatchEvent']['end_time_minutes']:'00');
		}
	
		if(parent::save('SODespatchEvent')) {
			sendTo('sodespatchevents','index',array('despatch'));
		} else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
	
	public function getEvents() {
		$despatch_events=new SODespatchEventCollection();
		$sh = new SearchHandler($despatch_events, false);
		$sh->addConstraint(new Constraint('end_time', '>=', date('Y-m-d H:i:s', $this->_data['start'])));
		$sh->addConstraint(new Constraint('start_time', '<', date('Y-m-d H:i:s', $this->_data['end'])));
		$sh->addConstraint(new Constraint('status', 'NOT IN', "('X')"));
		$despatch_events->load($sh);
		
		$events=array();
		$output_events=array();
		
		$events=$despatch_events->getArray();
		
		$colours=array('tc'=>'fc_red',
					   'tnc'=>'fc_green',
					   'nbi'=>'fc_pink',
					   'led'=>'fc_yellow'
		);
		
		$accessobject=AccessObject::Instance();
		$editable=$accessobject->hasPermission('despatch','sodespatchevents','edit');
		
		// pardon my ignorance, but we shouldn't have to check is an array is empty... right?
		if(!empty($events)) {
			foreach($events as $key=>$value) {
				
				$output_events[]=array('id'=>$value['id'],
								'title'=>$value['title'],
								'allDay'=>false,
								'start'=>strtotime($value['start_time']),	
								'end'=>strtotime($value['end_time']),
								'className'=>$colours[strtolower($value['status'])],
								'editable'=>$editable
						  );
			}
		}
	
		echo json_encode($output_events);
		exit;
		
	}
	public function saveEvent() {
		
		if(isset($this->_data['SODespatchEvent']['start_time'])) {
			$this->_data['SODespatchEvent']['start_time'].=(!empty($this->_data['SODespatchEvent']['start_time_hours'])?' '.$this->_data['SODespatchEvent']['start_time_hours']:' 00');
			$this->_data['SODespatchEvent']['start_time'].=':';
			$this->_data['SODespatchEvent']['start_time'].=(!empty($this->_data['SODespatchEvent']['start_time_minutes'])?$this->_data['SODespatchEvent']['start_time_minutes']:'00');
		}
		if(isset($this->_data['SODespatchEvent']['end_time'])) {
			$this->_data['SODespatchEvent']['end_time'].=(!empty($this->_data['SODespatchEvent']['end_time_hours'])?' '.$this->_data['SODespatchEvent']['end_time_hours']:' 00');
			$this->_data['SODespatchEvent']['end_time'].=':';
			$this->_data['SODespatchEvent']['end_time'].=(!empty($this->_data['SODespatchEvent']['end_time_minutes'])?$this->_data['SODespatchEvent']['end_time_minutes']:'00');
		}
		
		$error=array();
		
		if(parent::save('SODespatchEvent','',$error)) {
			json_reply(array('success' => TRUE));
		} else {
			json_reply(array('success' => FALSE));
		}
		
	}
	
	public function updateEvent() {
				
		$event = $this->_uses['SODespatchEvent'];
		$event->load($this->_data['id']);
		
		$data=$event->_data;
		
		// I'm sure there's a better way to format these dates... 
		switch($this->_data['type']) {
			case "drop":
				$data['start_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['start_time'])))),true);
				$data['end_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['end_time'])))),true);
				break;
			case "resize":
				$data['start_time']=un_fix_date($data['start_time'],true);
				$data['end_time']=un_fix_date(date('o-m-d H:i:s', strtotime("+".$this->_data['day']." day",strtotime("+".$this->_data['minute']." minute",strtotime($data['end_time'])))),true);
				break;
		}
		
		$errors=array();
		
		if(parent::save('SODespatchEvent',$data,$errors)) {
			json_reply(array('success' => TRUE));
		} else {
			json_reply(array('success' => FALSE));
		}
		
	}
	
}
?>
