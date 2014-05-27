<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EquipmentController extends Controller {

	protected $version='$Revision: 1.3 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		
		$this->_templateobject = new ProjectEquipment();
		$this->uses($this->_templateobject);
		
	}
	
	public function index() {
		$this->view->set('clickaction', 'view');
		
		parent::index(new ProjectEquipmentCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'),
					'tag'=>'New Equipment'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new() {
		parent::_new();
	}
	
	public function save() {
		$flash=Flash::Instance();
		if(parent::save($this->modeltype))
			sendTo($this->name, 'view', $this->_modules, array('id'=>$this->_data['id']));
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$equipment=$this->_uses[$this->modeltype];
		
		$sidebar=new SidebarController($this->view);
		$sidebar->addList(
			'currently_viewing',
			array(
				$equipment->name => array(
					'tag' => $equipment->name,
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'view'
								   ,'id'=>$equipment->id)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'edit'
								   ,'id'=>$equipment->id)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'delete'
								   ,'id'=>$equipment->id)
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
}
?>