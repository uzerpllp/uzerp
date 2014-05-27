<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

System::references('contacts');

class DetailsController extends PersonsController {

	
	function __construct($module=null,$action=null) {
		parent::__construct($module,$action);
		$this->uses(new User);
		$this->uses(new Person);
	}

	function index() {
		$user=$this->_uses['User'];
		$user->load(EGS_USERNAME);
		$sidebarlist=array();
		if ($user && !is_null($user->person_id)) {
			$this->_data['id']=$user->person_id;
			parent::view();
			$person=$this->_uses['Person'];
			System::references('contacts', 'template', 'persons');
			$this->_templateName=$this->getTemplateName('view');
			$sidebarlist[$person->fullname]=array('tag'=>$person->fullname
												 ,'link'=>array('module'=>'dashboard','controller'=>'details')
												 );
			$sidebarlist['Permissions']=array('tag'=>'Permissions'
											 ,'link'=>array('module'=>'dashboard','controller'=>'details','action'=>'permissions','username'=>EGS_USERNAME)
											 );
			$sidebar=new SidebarController($this->view);
			$sidebar->addList('currently_viewing', $sidebarlist);
			$this->view->register('sidebar',$sidebar);
			$this->view->set('sidebar',$sidebar);
		} else {
			sendTo('details', 'permissions', 'dashboard',array('username'=>EGS_USERNAME));
		}
	}

	function edit() {
		$user=$this->_uses['User'];
		$user->load(EGS_USERNAME);
		$this->_data['id']=$user->person_id;
		parent::edit();
		System::references('contacts', 'template', 'persons');
		$this->_templateName=$this->getTemplateName('new');
	}

	function save() {
		$user=$this->_uses['User'];
		$user->load(EGS_USERNAME);
		if($this->_data['Person']['id']==$user->person_id) {
			parent::save();			
		}
		else {
			$flash=Flash::Instance();
			$flash->addError('You don\'t have permission to do that');
			sendBack();
		}
	}

	function permissions () {
		$user=$this->_uses['User'];
		$user->load(EGS_USERNAME);
		
		$this->view->set('companies', $user->getCompanies());

		$this->view->set('roles', $user->getCompanyRoles());
						
	}
	
}
?>