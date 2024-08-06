<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AdminSearch extends BaseSearch
{

	protected $version='$Revision: 1.8 $';

	protected $fields=array();

	public static function HasRole($search_data = null, &$errors = [], $defaults = null)
	{
		$search = new AdminSearch($defaults);

// Search by User
		$search->addSearchField(
			'username',
			'username',
			'select',
			'',
			'advanced'
		);
		$user = DataObjectFactory::Factory('User');
		$options = array('' => 'All');
		$users = $user->getAll();
		$options += $users;
		$search->setOptions('username', $options);


// Search by Role
		$search->addSearchField(
			'roleid',
			'role',
			'select',
			'',
			'advanced'
		);
		$role = DataObjectFactory::Factory('Role');
		$options = array('' => 'All');
		$roles = $role->getAll();
		$options += $roles;
		$search->setOptions('roleid', $options);

		$search->setSearchData($search_data,$errors,'HasRole');
		return $search;
	}

	public static function HasPermission($search_data = null, &$errors = [], $defaults = null)
	{
		$search = new AdminSearch($defaults);

// Search by User
		$search->addSearchField(
			'permissionsid',
			'permission',
			'select',
			'',
			'advanced'
		);
		$permission = DataObjectFactory::Factory('Permission');
		$options = array('' => 'All');
		$permissions = $permission->getAll();
		$options += $permissions;
		$search->setOptions('permissionsid', $options);


// Search by Role
		$search->addSearchField(
			'roleid',
			'role',
			'select',
			'',
			'advanced'
		);
		$role = DataObjectFactory::Factory('Role');
		$options = array('' => 'All');
		$roles = $role->getAll();
		$options += $roles;
		$search->setOptions('roleid', $options);

		$search->setSearchData($search_data,$errors,'HasPermission');
		return $search;
	}

	public static function CompanyRole($search_data = null, &$errors = [], $defaults = null)
	{
		$search = new AdminSearch($defaults);

// Search by User
		$search->addSearchField(
			'company_id',
			'company',
			'select',
			'',
			'advanced'
		);
		$company = DataObjectFactory::Factory('User');
		$options = array('' => 'All');
		$companies = $company->getAll();
		$options += $companies;
		$search->setOptions('company_id', $options);


// Search by Role
		$search->addSearchField(
			'role_id',
			'role',
			'select',
			'',
			'advanced'
		);
		$role = DataObjectFactory::Factory('Role');
		$options = array('' => 'All');
		$roles = $role->getAll();
		$options += $roles;
		$search->setOptions('role_id', $options);

		$search->setSearchData($search_data,$errors,'CompanyRole');
		return $search;
	}

	public static function ObjectRole($search_data = null, &$errors = [], $defaults = null)
	{
		$search = new AdminSearch($defaults);

		if (isset($search_data['object_type'])) {
			$objecttype=$search_data['object_type'];
// Search by Object
			$search->addSearchField(
				'object_id',
				$objecttype,
				'select',
				'',
				'advanced'
			);
			$object = DataObjectFactory::Factory($objecttype);
			$options = array('' => 'All');
			$objects = $object->getAll();
			$options += $objects;
			$search->setOptions('object_id', $options);
		} else {
			$objecttype='';
		}

// Search by Role
		$search->addSearchField(
			'role_id',
			'role',
			'select',
			'',
			'advanced'
		);
		$role = DataObjectFactory::Factory('Role');
		$options = array('' => 'All');
		$roles = $role->getAll();
		$options += $roles;
		$search->setOptions('role_id', $options);

		$search->setSearchData($search_data,$errors,'ObjectRole');
		return $search;
	}

	public static function Users($search_data = null, &$errors = [], $defaults = null)
	{
		$search = new AdminSearch($defaults);

		// Search by User
		$search->addSearchField(
			'username',
			'User Name',
			'contains',
			'',
			'basic'
		);

		// Search by User
		$search->addSearchField(
			'person_id',
			'Person',
			'select',
			'',
			'advanced'
		);
		$person = DataObjectFactory::Factory('User');
		$options = array('' => 'All');
		$cc = new ConstraintChain();

		$person->idField	= 'person_id';
		$person->identifierField	= 'person';

		$cc = new ConstraintChain();
		$cc->add(new Constraint('person_id', 'is not', 'NULL'));

		$people = $person->getAll($cc, FALSE, TRUE);
		$options += $people;
		$search->setOptions('person_id', $options);


		// Search by Access Enabled
		$search->addSearchField(
			'access_enabled',
			'access_enabled',
			'select',
			'TRUE',
			'advanced'
		);
		$search->setOptions('access_enabled', array(''		=> 'All'
												   ,'TRUE'	=> 'True'
												   ,'FALSE'	=> 'False'));

		$search->setSearchData($search_data, $errors, 'Users');

		return $search;
	}

}

// End of AdminSearch
