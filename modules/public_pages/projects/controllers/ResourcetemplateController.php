<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ResourcetemplateController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Resourcetemplate();
		$this->uses($this->_templateobject);
	}
	
	public function index($collection = null, $sh = '', &$c_query = null) {
		parent::index(new ResourcetemplateCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'projects','controller'=>'resourcetemplate','action'=>'new'),
					'tag'=>'new_resource_template'
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);		
		$this->view->set('no_delete', false);
		$this->view->set('clickaction', 'view');
	}
	
	public function _new() {
		parent::_new();
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		if(parent::save('Resourcetemplate')) {
			sendBack();
		}
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}
	}
	
	public function view() {
		$resourcetemplate=$this->_uses['Resourcetemplate'];
		$resourcetemplate->load($this->_data['id']) or sendBack();
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'currently_viewing',
			array(
				$resourcetemplate->person_id => array(
					'tag' => $resourcetemplate->person . ' (' . $resourcetemplate->name . ')',
					'link' => array(
						'module'=>'projects',
						'controller'=>'resourcetemplate',
						'action'=>'view',
						'id'=>$resourcetemplate->id
					)
				),
				'edit' => array(
					'tag' => 'Edit',
					'link' => array(
						'module'=>'projects',
						'controller'=>'resourcetemplate',
						'action'=>'edit',
						'id'=>$resourcetemplate->id
					)
				),
				'delete' => array(
					'tag' => 'Delete',
					'link' => array(
						'module'=>'projects',
						'controller'=>'resourcetemplate',
						'action'=>'delete',
						'id'=>$resourcetemplate->id
					)
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
	}
	
	public function delete($modelName = null){
		$flash = Flash::Instance();
		if (!isModuleAdmin()) {
			$flash->addError('Sorry, must be a module admin to delete resource templates.');
			sendBack();
		}
		parent::delete('Resourcetemplate');
		sendTo('resourcetemplate','index','projects');
	}
}
?>