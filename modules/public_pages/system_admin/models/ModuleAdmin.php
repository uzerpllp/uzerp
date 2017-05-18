<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleAdmin extends DataObject {

	protected $defaultDisplayFields = array('module_name'
										   ,'role');
	
	function __construct($tablename='module_admins') {
		parent::__construct($tablename);
		$this->identifierField='module_name';
 		$this->belongsTo('Role', 'role_id', 'role'); 
		
	}
	
	function getModuleName($role_id) {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('role_id','=',$role_id));
		return $this->getAll($cc);				
	}

	function getModuleNames($role_id) {
		$this->idField='module_name';
		$this->identifierField='role_id';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('role_id','=',$role_id));
		return $this->getAll($cc);				
	}

}
?>