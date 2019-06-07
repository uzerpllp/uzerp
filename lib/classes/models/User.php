<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class User extends DataObject
{

	protected $version = '$Revision: 1.17 $';

	protected $defaultDisplayFields = array('person'		 => 'Person'
										   ,'company'		 => 'Company'
										   ,'last_login'	 => 'Last Login'
										   ,'audit_enabled'	 => 'Audit'
										   ,'debug_enabled'	 => 'Debug'
										   ,'access_enabled' => 'Enabled');

	function __construct($tablename = 'users')
	{

		parent::__construct($tablename);

		$this->idField			= 'username';
		$this->identifierField	= 'username';
		$this->orderby			= 'username';

		$this->isHandled('lastcompanylogin');

		$this->belongsTo('Person','person_id','person');
		$this->belongsTo('Systemcompany','lastcompanylogin','company');

		$this->hasOne('Systemcompany','lastcompanylogin','systemcompany');
		$this->hasOne('Person','person_id','persondetail');

		$this->hasMany('HasRole', 'roles', 'username');
		$this->hasMany('Usercompanyaccess', 'companies', 'username');

		$this->validateUniquenessOf('person_id');
	}

	public function isField($value, $depth = 1)
	{
		if ($value == 'usercompanyid')
		{
			return true;
		}
		else
		{
			return parent::isField($value,$depth);
		}
	}

	function getActive(ConstraintChain $cc = null, $ignore_tree = false)
	{

		if (empty($cc))
		{
			$cc = new ConstraintChain();
		}

		$cc->add(new Constraint('access_enabled', 'is', TRUE));

		return $this->getAll($cc, $ignore_tree);
	}

	function getAll(ConstraintChain $cc = null, $ignore_tree = false)
	{
		$db = DB::Instance();

		$tablename = $this->_tablename;

		if (empty($cc))
		{
			$cc = new ConstraintChain();
		}

		$collection_name = 'UserCollection';

		$coln = new $collection_name;

		$tablename = $coln->_tablename;

		$cc->add(new Constraint('usercompanyid','=',EGS_COMPANY_ID));

		$query = 'SELECT '.$this->idField.' as id, '.$this->getIdentifier().' FROM '.$tablename;

		$constraint = $cc->__toString();

		if (!empty($constraint))
		{
			$query .= ' WHERE '. $constraint;
		}

		$query .= ' ORDER BY username';

		$results = $db->GetAssoc($query);

		if($this->idField==$this->getIdentifier() && $results)
		{
			foreach($results as $key=>$nothing)
			{
				$results[$key] = $key;
			}
		}
		return $results;
	}

	function getAllUsers(ConstraintChain $cc = null, $ignore_tree = false)
	{
		$db = DB::Instance();

		$tablename = $this->_tablename;

		if (empty($cc))
		{
			$cc = new ConstraintChain();
		}

		$query = 'SELECT '.$this->idField.', '.$this->getIdentifier().' FROM '.$this->_tablename;

		$constraint = $cc->__toString();

		if (!empty($constraint))
		{
			$query .= ' WHERE '. $constraint;
		}

		$query .= ' ORDER BY username';

		$results = $db->GetAssoc($query);

		if($this->idField==$this->getIdentifier() && $results)
		{
			foreach($results as $key => $nothing)
			{
				$results[$key] = $key;
			}
		}
		return $results;
	}

	public function load($clause, $override = false)
	{
		return parent::load($clause, true);
	}

	function loadBy($field,$value = null, $tablename = false)
	{
		$db = &DB::Instance();

		if($field instanceof SearchHandler)
		{
			$sh = $field;

			$sh->setLimit(1);

			$qb = new QueryBuilder($db);

			$query = $qb->select($sh->fields)
						->from($this->_tablename)
						->where($sh->constraints)
						->orderby($sh->orderby,$sh->orderdir)
						->limit($sh->perpage,$sh->offset)->__toString();
		}
		else
		{
			if($field instanceof ConstraintChain)
			{
				$where = $field->__toString();
			}
			elseif(!is_array($field)&&!is_array($value))
			{
				$where = $field.'='.$db->qstr($value);
			}
			elseif(!(is_array($field)&&is_array($value)))
			{
				throw new Exception('Error: $fieldname and $value must be of same type, array or string');
			}
			else
			{
				$where = '1=1';

				for($i=0;$i<count($field);$i++)
				{
					if ((!$tablename) && (($this->getField($field[$i])->type == 'numeric') || (substr($this->getField($field[$i])->type,0,3) == 'int')) && ($value[$i] == ''))
					{
						$where .= ' AND '.$field[$i].'=null';
					}
					else
					{
						$where .= ' AND '.$field[$i].'='.$db->qstr($value[$i]);
					}
				}

			}
			$where .= ' AND usercompanyid='.$db->qstr(EGS_COMPANY_ID);

			$query = 'SELECT * FROM useroverview WHERE '.$where;
		}

		$row = $db->GetRow($query);

		if($row===false)
		{
			die("Error in loadby: ".$db->ErrorMsg().$query);
		}

		if(count($row)>0)
		{
			$this->_data = $row;

			return $this->load($row[$this->idField]);
		}

		return false;
	}

	function getCount()
	{
		$db = &DB::Instance();

		$tablename = 'useroverview';

		$cc = new ConstraintChain();

		if ($this->isAccessControlled())
		{

			$cc->add(new Constraint('usernameaccess', '=', EGS_USERNAME));

			$collection_name = get_class($this).'Collection';

			$coln = new $collection_name;

			$tablename = $coln->_tablename;

		}

		if($this->isField('usercompanyid'))
		{
			$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		}

		$query = 'SELECT count(*) FROM '.$tablename;

		if (count($cc) > 0) {
			$query .= ' WHERE '.$cc->__toString();
		}

		$count=$db->GetOne($query);

		return $count;
	}

	/**
	 * @param $user mixed either a User model, or a username
	 * @param $roles array an array of role-ids
	 *
	 * Put a user into one or more roles
	 */
	public static function setRoles($user, $roles, &$errors = array())
	{
		if(!$user instanceof User)
		{
			$username = $user;

			$user = DataObjectFactory::Factory('User');

			$user->load($username);

			if(!$user->isLoaded())
			{
				return FALSE;
			}
		}

		$db = DB::Instance();

		$db->StartTrans();

		$query = "delete from hasrole where username=".$db->qstr($user->username)
				.' AND roleid IN (SELECT id FROM roles WHERE usercompanyid='.$db->qstr(EGS_COMPANY_ID).')';

		$db->Execute($query);

		foreach($roles as $role)
		{
			$ob = DataObject::Factory(array('roleid'=>$role, 'username'=>$user->username), $errors, 'HasRole');

			if(count($errors)>0 || !$ob || !$ob->save())
			{
				$db->FailTrans();
				$db->CompleteTrans();
				return false;
			}
 		}

 		$db->CompleteTrans();

		return true;
	}


	/**
	 *[ @param $password string ]
	 * @return string
	 * Set, and optionally generate (default), a password for the User
	 * Passwords are between 6 and 8 characters, and are purely alphanumeric
	 * The function returns the password (unhashed)
	 * @see User;:setPassword()
	 */
	public function setPassword($password = null, &$errors = array())
	{
		if($password===null || $password=='')
		{
		    $factory = new RandomLib\Factory;
		    $generator = $factory->getGenerator(new SecurityLib\Strength(SecurityLib\Strength::MEDIUM));

			$password = $generator->generateString(10);;
		}

		if (strlen($password) < 10) {
		    $errors[] = "Error setting password for user {$this->username}, new password must be at least 10 characters long";
		    return false;
		}

		self::updatePassword($password, $this->username);

		return $password;
	}


	/**
	 * @param $password string
	 * @param $username string
	 * @return boolean
	 *
	 * Takes a username and a password, and updates the user's password accordingly.
	 * The password is hashed before being inserted.
	 */
	public static function updatePassword($password, $username)
	{
		$db = DB::Instance();

		$user_data = array('username'=>$username, 'password'=>password_hash($password, PASSWORD_DEFAULT));

		return($db->Replace('users', $user_data, 'username', true)!==false);
	}

	public static function getOtherUsers()
	{
		$user = DataObjectFactory::Factory('User');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('username', '<>', EGS_USERNAME));

		return $user->getAll($cc);
	}

	public function getPersonName()
	{
		if (is_null($this->person_id))
		{
			return $this->username;
		}

		$person = DataObjectFactory::Factory('Person');

		$person->load($this->person_id);

		return $person->firstname.' '.$person->surname;
	}

	public function lastCompanyLogin()
	{
		$sc = DataObjectFactory::Factory('Systemcompany');

		$sc->load($this->lastcompanylogin, true);

		return $sc->companyName();
	}

	public function getCompanies ()
	{
		$companies = new UsercompanyaccessCollection();

		$sh = new SearchHandler($companies, false, false);

		$sh->addConstraint(new Constraint('username','=', $this->username));

		$companies->load($sh);

		return $companies;
	}

	public function getCompanyRoles ()
	{
		$companies = $this->getCompanies();

		$roles = array();

		foreach ($companies as $company)
		{
			$role_ids = array();

			foreach ($company->getRoles() as $role)
			{
				$role_ids[] = $role->id;
			}

			$roles[$company->usercompanyid] = $this->getRoles($role_ids);
		}

		return $roles;
	}

	function getAssignedPeople()
	{
		$this->idField = 'person_id';

		$cc = new ConstraintChain();

		$cc->add(new Constraint('person_id', 'is not', 'NULL'));

		$assigned_users = $this->getAll($cc);

		if ($this->isLoaded())
		{
			if (isset($assigned_users[$this->{$this->idField}]))
			{
				unset($assigned_users[$this->{$this->idField}]);
			}
		}

		return $assigned_users;
	}

	public function getRoles($role_ids = null)
	{
		$userroles = new HasRoleCollection();

		$sh = new SearchHandler($userroles, false, false);

		$sh->addConstraint(new Constraint('username', '=', $this->username));

		if (!empty($role_ids))
		{
			if (!is_array($role_ids))
			{
				$role_ids = array($role_ids);
			}

			$sh->addConstraint(new Constraint('roleid', 'in', '('.implode(',', $role_ids).')'));
		}

		$userroles->load($sh);

		return $userroles;

	}

}

// End of User
