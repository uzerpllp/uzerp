<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class StproductgroupsController extends ManufacturingController {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STProductgroup();
		$this->uses($this->_templateobject);
	
	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new STProductgroupCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Product Group'
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

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('Stock Product Groups');
	}

}
?>