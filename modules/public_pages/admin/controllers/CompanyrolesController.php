<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyrolesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CompanyRole();
		$this->uses($this->_templateobject);

	}

	public function index(){
		global $smarty;
		
		$s_data=array();
		
// Set context from calling module
		if (isset($this->_data['company_id'])) {
			$s_data['company_id'] = $this->_data['company_id'];
		}
		if (isset($this->_data['role_id'])) {
			$s_data['role_id'] = $this->_data['role_id'];
		}
		
		$this->setSearch('AdminSearch', 'CompanyRole', $s_data);
		
		parent::index(new CompanyRoleCollection($this->_templateobject));
		
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('CompanyRole');
		sendTo('CompanyRole','index',array('admin'));
	}
	
	public function view() {
		$companyrole=$this->_uses['CompanyRole'];
		$companyrole->load($this->_data[$companyrole->idField],true);

		$this->view->set('clickaction', 'viewuser');
	}

	public function save() {
	$flash=Flash::Instance();
	if(parent::save('CompanyRole'))
			sendTo('CompanyRole','index', array('admin'));
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}
}
?>
