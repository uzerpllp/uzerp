<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportPartsController extends PrintController {

	protected $version='$Revision: 1.2 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ReportPart();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$errors=array();

//		$s_data=array();
//		$this->setSearch('reportsSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'edit');
		$reports = new ReportPartCollection($this->_templateobject);
		parent::index($reports);

		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();
		$sidebarlist['new']=array('tag'=>'New Report Part'
							  ,'link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'new'
											)
				 			  );
				 			  
		$sidebar->addList(
			'Actions',
			$sidebarlist
			);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function edit() {
		parent::edit();
		
		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();
		$sidebarlist['action']=array('tag'=>'View All Report Parts'
							  ,'link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'index'
											)
				 			  );

		$sidebar->addList('Actions',$sidebarlist);
	
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
				
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$errors=array();
		
		$this->_data['ReportPart']['name']=strtolower($this->_data['ReportPart']['name']);
		
		if(parent::save('ReportPart','',$errors)) {
			sendBack();	
		} else {
			$flash->addErrors($errors);
			sendBack();
			
		}
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('report parts');
	}

}
?>