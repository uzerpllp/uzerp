<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DebugOption extends DataObject {

	function __construct($tablename='debug_options') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->setEnum('options', array('ao'=>'AccessObject'
									   ,'bs'=>'BaseSearch'
									   ,'c'=>'Controller'
									   ,'db'=>'DashboardController'
									   ,'dc'=>'DataObjectCollection'
									   ,'do'=>'DataObject'
									   ,'l'=>'lib'
									   ,'pc'=>'PermissionsController'
									   ,'rh'=>'RedirectHandler'
									   ,'s'=>'system'
									   ,'sp'=>'system:autoloader_paths'
									   ,'st'=>'system:template_paths'
									   ,'su'=>'system:url_info'
									   ,'sh'=>'SearchHandler'));
		
	}

	static function getDebugOption() {
		$debugoption=DebugOption::getUserOption(EGS_USERNAME);
		if (!$debugoption->isLoaded()) {
			$debugoption=DebugOption::getCompanyOption(EGS_COMPANY_ID);
		}
		return $debugoption;
	}

	static function getUserOption ($username) {
		$debugoption=new DebugOption();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('username' ,'=', $username));
		$cc->add(new Constraint('company_id' ,'=', EGS_COMPANY_ID));
		$debugoption->loadBy($cc);
		return $debugoption;
	}
	
	static function getCompanyOption ($companyname) {
		$debugoption=new DebugOption();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('username' ,'is', 'NULL'));
		$cc->add(new Constraint('company_id' ,'=', EGS_COMPANY_ID));
		$debugoption->loadBy($cc);
		return $debugoption;
	}
	
	function getOptions() {
		if ($this->isLoaded()) {
			return unserialize($this->options);
		} else {
			return array();
		}
	}
	
	function setOptions($options=array()) {
		return serialize($options);
	}
	
}
?>