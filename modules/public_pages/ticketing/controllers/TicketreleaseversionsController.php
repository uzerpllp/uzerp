<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketreleaseversionsController extends Controller {

	protected $version='$Revision: 1.1 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new TicketReleaseVersion();
		$this->uses($this->_templateobject);
		
	}
	
	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		parent::index(new TicketReleaseVersionCollection($this->_templateobject));
		
		$sidebarlist=array();
		
		$sidebarlist['all_tickets'] = array(
								'link'=>array('modules'=>$this->_modules ,'controller'=>'tickets', 'action'=>'view'),
								'tag'=>'View All Tickets'
								);
		
		$sidebarlist['new'] = array(
								'link'=>array('modules'=>$this->_modules, 'controller'=>$this->name, 'action'=>'new'),
								'tag'=>'New Release Version'
								);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			$sidebarlist
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		
		if (!$this->CheckParams($this->modeltype)) {
			sendBack();
		}
		
		$flash=Flash::Instance();
		$errors=array();
		
		if(parent::save($this->modeltype, '', $errors)) {
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		$flash->addErrors($errors);
		$flash->addError('Failed to save '.$this->modeltype);
		$this->refresh();
		
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Ticket Release Versions':$base), $action);
	}
	
}
?>