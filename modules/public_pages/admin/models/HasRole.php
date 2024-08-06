<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HasRole extends DataObject
{

	protected $version = '$Revision: 1.9 $';

	function __construct($tablename = 'hasrole')
	{
		$this->defaultDisplayFields = array('roleid'	=> 'Role ID'
										   ,'username'	=> 'Username'
										   ,'role'		=> 'Roles');

		parent::__construct($tablename);

		$this->idField			= 'id';
		$this->identifierField	= 'roleid';

		$this->belongsTo('Role', 'roleid', 'roles_roleid');
 		$this->belongsTo('User', 'username', 'users_username'); 

	}

	function getRoleID($username)
	{
		if (empty($username)) return [];
// Get the roles for the user company id
// Could simplify this by putting usercompanyid on hasrole?
		$role = DataObjectFactory::Factory('Role');

		$roles = $role->getAll();
		$role_ids = array_keys($roles);

		$cc = new ConstraintChain();

		$cc->add(new Constraint('username','=',$username));
		$cc->add(new Constraint('roleid','in','('.implode(',',$role_ids).')'));

// return the roles that the user is assigned to
		return $this->getAll($cc);

	}

	function getUsers($roleid)
	{
		if (empty($roleid)) return [];

		$cc = new ConstraintChain();

		if (is_array($roleid))
		{
			$cc->add(new Constraint('roleid', 'in', '(' . implode(',', $roleid) . ')'));
		}
		else
		{
			$cc->add(new Constraint('roleid', '=', $roleid));
		}

		$this->identifierField = 'username';

		return $this->getAll($cc);
	}

}

// End of HasRole
