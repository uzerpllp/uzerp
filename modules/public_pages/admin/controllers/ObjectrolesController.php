<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ObjectrolesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new ObjectRole();
		$this->uses($this->_templateobject);

	}

	public function index(){
		global $smarty;
		
		$s_data=array();
		
// Set context from calling module
		if (isset($this->_data['object_id'])) {
			$s_data['object_id'] = $this->_data['object_id'];
		}
		if (isset($this->_data['role_id'])) {
			$s_data['role_id'] = $this->_data['role_id'];
		}
		
		$this->search('AdminSearch', 'ObjectRole', $s_data);
		
		parent::index(new ObjectRoleCollection($this->_templateobject));
		
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('ObjectRole');
		sendTo('ObjectRole','index',array('admin'));
	}
	
	public function view() {
		$companyrole=$this->_uses['ObjectRole'];
		$companyrole->load($this->_data[$companyrole->idField],true);

		$this->view->set('clickaction', 'viewuser');
	}

	public function save() {
		$flash=Flash::Instance();
		if(parent::save('ObjectRole')) {
			sendTo('ObjectRole','index', array('admin'));
		} else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}

}
?>
