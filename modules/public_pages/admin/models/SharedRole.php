<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SharedRole extends DataObject {

	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='shared_roles') {
		parent::__construct($tablename);

 		$this->belongsTo('Role', 'roleid', 'roles_roleid');
	}

	function getRoleID($_username, $_object_type, $_accesstype) {
		$cc = new ConstraintChain();
		$cc->add(new Constraint('username','=',$_username));
		$cc->add(new Constraint('object_type','=',$_object_type));
		$cc->add(new Constraint('"'.$_accesstype.'"','is',true));

		$this->idField='role_id';
		$this->identifierField='role_id';
		
		return $this->getAll($cc);

	}

}
?>
