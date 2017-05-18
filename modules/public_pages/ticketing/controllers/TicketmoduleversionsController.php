<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketmoduleversionsController extends Controller {

	protected $version='$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new TicketModuleVersion();
		$this->uses($this->_templateobject);
		
	}
	
	public function index(){
		$this->view->set('clickaction', 'view');
		parent::index(new TicketModuleVersionCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'ticketing','controller'=>'tickets','action'=>'view'),
					'tag'=>'View All Tickets'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
}
?>