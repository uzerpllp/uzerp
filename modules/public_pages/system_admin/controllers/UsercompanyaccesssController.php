<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class UsercompanyaccesssController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Usercompanyaccess();
		$this->uses($this->_templateobject);
	
		

	}

	public function _new() {
//		$SystemcompanyCollection = new SystemcompanyCollection;
//		$this->view->set('options',$SystemcompanyCollection->getCompanies());
//		$user = new User;
//		$this->view->set('users',$user->getAll());
		$Systemcompany=new Systemcompany();
		$users=$Systemcompany->getNonUsers();
		$system=system::Instance();

		if ($users) {
			$this->view->set('users',$users);
		} elseif (strtolower($system->action)!='edit') {
			$flash=Flash::Instance();
			$flash->addMessage('All users have been allocated to this company');
			sendBack();
		}
		parent::_new();
	}

	public function edit() {
		parent::edit();
		$this->view->set('edit',true);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		parent::index(new UsercompanyaccessCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'system_admin','controller'=>'Usercompanyaccesss','action'=>'new'),
					'tag'=>'new_user_company_access'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('Usercompanyaccess');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		if(parent::save('Usercompanyaccess')) {
			sendBack();
		}
		else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}

	protected function getPageName($base=null,$type=null) {
		return parent::getPageName((empty($base)?'User Company Accesss':$base),$type);
	}

}
?>
