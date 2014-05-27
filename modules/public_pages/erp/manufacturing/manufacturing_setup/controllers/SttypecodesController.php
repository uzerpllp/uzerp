<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SttypecodesController extends ManufacturingController {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STTypecode();
		$this->uses($this->_templateobject);
	
	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new STTypecodeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Stock Type Code'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  )
												)
							)
				 )
			);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		parent::_new();
		$whaction=new WHAction();
		$this->view->set('backflush_actions',$whaction->getActions('B'));
		$this->view->set('complete_actions',$whaction->getActions('C'));
		$this->view->set('issue_actions',$whaction->getActions('I'));
		$this->view->set('despatch_actions',$whaction->getActions('D'));
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('Stock Type Codes');
	}

}
?>