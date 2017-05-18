<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TaxstatussController extends LedgerController {

	protected $version='$Revision: 1.7 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new TaxStatus();
		$this->uses($this->_templateobject);

	}

	public function index(){
		$this->view->set('clickaction', 'edit');
		parent::index(new TaxStatusCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'new'
											 )
									   ),
					'tag'=>'new_tax_status'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
}
?>
