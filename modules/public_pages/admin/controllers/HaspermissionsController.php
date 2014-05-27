<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HaspermissionsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new HasPermission();
		$this->uses($this->_templateobject);

	}

	public function index(){
		global $smarty;
		
		$s_data=array();

// Set context from calling module
		if (isset($this->_data['permissionsid'])) {
			$s_data['permissionsid'] = $this->_data['permissionsid'];
		}
		if (isset($this->_data['roleid'])) {
			$s_data['roleid'] = $this->_data['roleid'];
		}
		
		$this->setSearch('AdminSearch', 'HasPermission', $s_data);
		
		parent::index(new HasPermissionCollection($this->_templateobject));
		
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('HasPermission');
		sendTo('HasPermissions','index',array('admin'));
	}
	
	public function save() {
	$flash=Flash::Instance();
	if(parent::save('HasPermission'))
			sendTo('HasPermissions','index', array('admin'));
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}
}
?>
