<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketconfigurationsController extends TicketingController {

	protected $version='$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);

		$this->_templateobject = new TicketConfiguration();
		$this->uses($this->_templateobject);
		
	}
	
	public function index() {
		$errors=array();
	
//		$this->setSearch('TicketsSearch', 'useDefault');

		parent::index(new TicketConfigurationCollection($this->_templateobject));
		
				
		$this->view->set('clickaction', 'view');

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Configuration'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	

	protected function getPageName($base=null,$type=null) {
		return parent::getPageName((empty($base)?'Ticketing Defaults':$base),$type);
	}

}
?>
