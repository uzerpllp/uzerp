<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanypermissionsController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CompanyPermission();
		$this->uses($this->_templateobject);
	
		

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new CompanyPermissionCollection($this->_templateobject));
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('CompanyPermission');
		sendTo('CompanyPermissions','index',array('admin'));
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
	$flash=Flash::Instance();
	if(parent::save('CompanyPermission'))
			sendTo('CompanyPermissions','index', array('admin'));
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}
}
?>
