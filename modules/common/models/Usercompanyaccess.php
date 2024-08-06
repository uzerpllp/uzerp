<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Usercompanyaccess extends DataObject {

	protected $version = '$Revision: 1.7 $';

	protected $defaultDisplayFields = array(
		'username'	=> 'Username',
		'enabled'	=> 'Enabled'
	);

	function __construct($tablename = 'user_company_access')
	{

		parent::__construct($tablename);

		$this->idField = 'id';

 		$this->validateUniquenessOf(array('username', 'usercompanyid'));
 		$this->belongsTo('User', 'username', 'user');
 		$this->hasOne('Systemcompany', 'usercompanyid', 'systemcompany');

//		$cc = new ConstraintChain();
//		$this->setAlias('systemcompany','Systemcompany',$cc,'', array(),'id');

	}

	function getRoles ()
	{
		$roles = new RoleCollection(new Role);

		$sh = new SearchHandler($roles, false, false);
		$sh->addConstraint(new Constraint('usercompanyid','=', $this->usercompanyid));

		$roles->load($sh);

		return $roles;

	}

	function getCompanies($username = '')
	{

		$cc = new ConstraintChain();

		if (!empty($username))
		{
			$cc->add(new Constraint('username', '=', $username));
		}
		$cc->add(new Constraint('enabled', 'is', true));

		return $this->getAll($cc);

	}
}

// end of Usercompanyaccess.php