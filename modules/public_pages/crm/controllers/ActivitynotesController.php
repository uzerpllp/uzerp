<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ActivitynotesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ActivityNote();
		$this->uses($this->_templateobject);
	
		

	}

	public function index(){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new ActivityNoteCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'ActivityNotes','action'=>'new'),
					'tag'=>'new_ActivityNote'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('ActivityNote');
		sendBack();
	}
	
	public function save() {
		$flash=Flash::Instance();
		if(parent::save('ActivityNote'))
			sendBack();
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
}
?>
