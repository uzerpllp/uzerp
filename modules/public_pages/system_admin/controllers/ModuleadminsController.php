<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleadminsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ModuleAdmin();
		$this->uses($this->_templateobject);
	
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		global $smarty, $module, $submodule;
		$this->view->set('clickaction', 'edit');
		$this->view->set('orderby', 'module_name');
		parent::index(new ModuleAdminCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>$module
								,'submodule'=>$submodule
								,'controller'=>'moduleadmins'
								,'action'=>'new'),
					'tag'=>'New Module Admin'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('ModuleAdmin');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
	$flash=Flash::Instance();
	if(parent::save('ModuleAdmin'))
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('Admin Modules');
	}
}
?>
