<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExternalsystemsController extends EdiController {

	protected $_templateobject;
	protected $version='$Revision: 1.5 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ExternalSystem();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new ExternalSystemCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new external system'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view () {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$system=$this->_uses[$this->modeltype];
		$this->view->set('externalsystem', $system);
		
		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();
		$sidebarlist['View All'] = array(
					'tag'=>'View All Systems',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				);
		$sidebarlist['new'] = array(
					'tag'=>'new external system',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 )
				);
		$sidebar->addList('actions', $sidebarlist);
		
		$sidebarlist=array();
		$sidebarlist[$system->name] = array(
					'tag' => 'view',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view'
								 ,'id'=>$system->id
								 )
				);
		$sidebarlist['edit'] = array(
					'tag'=>'edit',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'edit'
								 ,'id'=>$system->id
								 )
				);
		$sidebarlist['delete'] = array(
					'tag'=>'delete',
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'delete'
								 ,'id'=>$system->id
								 )
				);
		
		$sidebar->addList($system->name, $sidebarlist);
		
		$this->sidebarRelatedItems($sidebar, $system);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
				
	}
	
	protected function getPageName($base='',$action='') {
		return parent::getPageName((empty($base)?'external systems':$base),$action);
	}

}
?>
