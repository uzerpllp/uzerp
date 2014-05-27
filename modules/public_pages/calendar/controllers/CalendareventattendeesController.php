<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CalendareventattendeesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CalendarEventAttendee();
		$this->uses($this->_templateobject);
	
		

	}

	public function index(){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new CalendarEventAttendeeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'calendar','controller'=>'CalendarEventAttendees','action'=>'new'),
					'tag'=>'new_Event_Attendee'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('CalendarEventAttendee');
		sendBack();
	}
	
	public function save() {
		$flash=Flash::Instance();
		if(parent::save('CalendarEventAttendee'))
			sendBack();
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
}
?>
