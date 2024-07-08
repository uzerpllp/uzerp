<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class InjectorclasssController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new InjectorClass();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		
		// Search
		$errors=array();
	
		$s_data=array();

// Set context from calling module
		$s_data['name']='';
		$s_data['class_name']='';
		$s_data['category']='';
				
		$this->setSearch('InjectorSearch', 'useDefault', $s_data);
		
		parent::index(new InjectorClassCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'system_admin','controller'=>'Injectorclasss','action'=>'new'),
					'tag'=>'New Injector Class'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('', 'List'));
		$this->view->set('clickaction', 'edit');
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null) {

		parent::delete('InjectorClass');
		sendTo('Injectorclasss','index',array('system_admin'));
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		parent::save('InjectorClass');
		sendTo('Injectorclasss','index',array('system_admin'));
	}	

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Injector Class':$base), $action);
	}

}
?>
