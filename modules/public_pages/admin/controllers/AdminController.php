<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class AdminController extends Controller {
	
	function __construct($action=null) {
		$this->uses('User');
		parent::__construct($action);
	}

	function index(\DataObjectCollection $collection, $sh = '', &$c_query = \null) {
		static $menuaction='User Overview';
		global $smarty;
		$users=new UserCollection(new User);
		$users->load();
		$smarty->assign('users',$users);
	}
	public function Newuser() {
		static $menuaction='New User';
		global $smarty;
		$models = [];
		foreach($this->_uses as $model) {
			$models[get_class($model)]=$model;
		}
		$smarty->assign('models',$models);
	}
	
	public function Edituser() {
		$id=$this->_data['username'];
		$this->Newuser();
		$this->_templateName=$this->getTemplateName('newuser');
		$user=&$this->_uses['User'];
		$user->load($id);
	}
	
	public function Saveuser() {
		$user=User::Factory($this->_data['User'],$errors);
		$flash=Flash::Instance();
		$success = $user->save();
		if($success!==false) {
			$flash->addMessage('User saved successfully');
			sendTo('admin');
		}
		else {
			$flash->addErrors($errors);
			$this->Newuser();			
		}
	}
}
?>