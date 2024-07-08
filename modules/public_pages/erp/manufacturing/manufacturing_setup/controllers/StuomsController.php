<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class StuomsController extends ManufacturingController {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STuom();
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		$this->view->set('orderby', 'uom_name');
		parent::index(new STuomCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Unit of Measure'
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
		return parent::getPageName('Units of Measure');
	}

}
?>