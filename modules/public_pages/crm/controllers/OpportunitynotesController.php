<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OpportunitynotesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new OpportunityNote();
		$this->uses($this->_templateobject);
	
		

	}

	public function index(){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new OpportunityNoteCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'OpportunityNotes','action'=>'new'),
					'tag'=>'new_OpportunityNote'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('OpportunityNote');
		sendBack();
	}
	
	public function save() {
		$flash=Flash::Instance();
		if(parent::save('OpportunityNote'))
			sendBack();
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
}
?>
