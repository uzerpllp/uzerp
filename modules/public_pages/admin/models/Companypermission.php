<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Companypermission extends DataObject {

	function __construct($tablename='companypermissions') {
		parent::__construct($tablename);
		$this->identifierField='permissionid';
		$this->idField='id';
		$this->belongsTo('Permission', 'permissionid', 'permission');
		$this->belongsTo('Systemcompany', 'usercompanyid', 'company');
	
	}
	
	function getPermissionID($systemcompany) {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('usercompanyid','=',$systemcompany));
		
		return $this->getAll($cc);

	}

}
?>
