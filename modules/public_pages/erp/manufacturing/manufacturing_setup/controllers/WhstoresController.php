<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhstoresController extends ManufacturingController {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHStore();
		$this->uses($this->_templateobject);
	}

	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new WHStoreCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'WHStores'
											 ,'action'=>'new'
											 )
									   ),
					'tag'=>'New Store'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		parent::_new();
		$this->view->set('addresses', WHStore::getAddresses());
	}
	
	public function view () {
		sendTo('WHLocations','index',$this->_modules,array('whstore_id'=>$this->_data['id']));
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'stores':$base), $action);
	}
	
}
?>
