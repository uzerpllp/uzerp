<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CompanyRole extends DataObject {

	function __construct($tablename='companyroles') {
		$this->defaultDisplayFields = array('company'
										   ,'role'
										   ,'read'
										   ,'write');
		
		parent::__construct($tablename);
//		$this->idField='companyid || roleid || username AS id';

 		$this->belongsTo('Role', 'role_id', 'role');
 		$this->belongsTo('Company', 'company_id', 'company'); 
	}

	function getRoleID($company_id, $accesstype) {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('company_id','=',$company_id));
		$cc->add(new Constraint('"'.$accesstype.'"','is',true));

		$this->idField='role_id';
		$this->identifierField='role_id';
		
		return $this->getAll($cc);

	}

}
?>
