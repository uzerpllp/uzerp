<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HasPermission extends DataObject {

/*
	HasPermission links Roles to Permissions
	-	Roles are linked to users through HasRole
	-	Permissions are linked to Companies through Companypermission

	The getRoleID and getPermissionID therefore need to take permission and role IDs
	as parameters so we can get the Roles/Permissions depending on the roles assigned
	to one or more users and the permissions assigned to one or more companies
*/
	 
	function __construct($tablename='haspermission') {
		$this->defaultDisplayFields = array('roleid'=>'Role ID'
										   ,'permissionsid'=>'Permission ID'
										   ,'role'=>'role'
										   ,'permission'=>'Permission');
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='roleid';
		
 		$this->belongsTo('Permission', 'permissionsid', 'role_permissionsid');
 		$this->belongsTo('Role', 'roleid', 'role_roleid'); 
 		$this->hasOne('Role', 'roleid', 'role'); 
 		$this->hasOne('Permission', 'permissionsid', 'perm');
 		
	}

	function getPermissionID($permissions=null, $roles=null) {
// Returns the permission ids for the matching role/permissions id(s)
		$cc = new ConstraintChain();

		if (!empty($permissions)) {
			if (is_array($permissions)) {
				$cc->add(new Constraint('permissionsid','in','('.implode(',', $permissions).')'));
			} else {
				$cc->add(new Constraint('permissionsid','=',$permissions));
			}
		}
	
		if (!empty($roles)) {
			if (is_array($roles)) {
				$cc->add(new Constraint('roleid','in','('.implode(',', $roles).')'));
			} else {
				$cc->add(new Constraint('roleid','=',$roles));
			}
		}
		
		$this->identifierField='permissionsid';
		return $this->getAll($cc);

	}

	function getRoleID($permissions=null, $roles=null) {
// Returns the role ids for the matching role/permissions id(s)
		$cc = new ConstraintChain();
		
		if (!empty($permissions)) {
			if (is_array($permissions)) {
				$cc->add(new Constraint('permissionsid','in','('.implode(',', $permissions).')'));
			} else {
				$cc->add(new Constraint('permissionsid','=',$permissions));
			}
		}
	
		if (!empty($roles)) {
			if (is_array($roles)) {
				$cc->add(new Constraint('roleid','in','('.implode(',', $roles).')'));
			} else {
				$cc->add(new Constraint('roleid','=',$roles));
			}
		}
				
		return $this->getAll($cc);

	}

	function getPermissionRoleID($permissions=null, $roles=null) {
// Returns the permission/role ids for the matching role/permissions id(s)
		$cc = new ConstraintChain();
		$this->idField='permissionsid';
		
		if (!empty($permissions)) {
			if (is_array($permissions)) {
				$cc->add(new Constraint('permissionsid','in','('.implode(',', $permissions).')'));
			} else {
				$cc->add(new Constraint('permissionsid','=',$permissions));
			}
		}
	
		if (!empty($roles)) {
			if (is_array($roles)) {
				$cc->add(new Constraint('roleid','in','('.implode(',', $roles).')'));
			} else {
				$cc->add(new Constraint('roleid','=',$roles));
			}
		}
				
		return $this->getAll($cc);

	}

}
?>
