<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Role extends DataObject
{

	protected $version='$Revision: 1.13 $';

	function __construct($tablename='roles')
	{
		$this->defaultDisplayFields = array('name'=>'Permission Name','description'=>'Description');
		parent::__construct($tablename);
		$this->idField='id';
		$this->validateUniquenessOf('name');
		$this->identifierField='name';
		$this->orderby = 'name';
		$this->hasMany('HasReport', 'reports', 'role_id');
		$this->hasMany('HasRole', 'users', 'roleid');
		$this->hasMany('HasPermission', 'permissions', 'roleid');
		$this->hasMany('CompanyRole', 'companies', 'role_id');
		$this->belongsTo('Systemcompany', 'usercompanyid', 'company');
 		$this->hasMany('ModuleAdmin', 'modules', 'role_id');

	}

	public function setPermissions($permission_ids, &$errors=array())
	{
		$db = DB::Instance();
		$db->StartTrans();

		$hp=new HasPermission();
		$hp->idField='roleid';

		if (!$hp->delete($this->id))
		{
			$errors[]='Failed to update role permissions';
		}

		if (count($errors)===0)
		{
			foreach($permission_ids as $id)
			{
				if(empty($id))
				{
					continue;
				}
				$permission = DataObject::Factory(array('roleid'=>$this->id, 'permissionsid'=>$id), $errors, 'HasPermission');
				if (count($errors)===0 && $permission)
				{
					if ($permission->save())
					{
						continue;
					}
				}
				$errors[]='Failed to update role permissions';
				break;
			}
		}

		if (count($errors)>0)
		{
			$db->CompleteTrans();
			return false;
		}

		return $db->CompleteTrans();

	}

	public function setAdmin($module_ids)
	{

		$db = DB::Instance();
		$db->StartTrans();

		$query = "delete from module_admins where role_id=".$db->qstr($this->id);
		$db->Execute($query);

		foreach ($module_ids as $key=>$admin)
		{
			$query = "insert into module_admins(role_id,module_name) values (".$db->qstr($this->id).",".$db->qstr($key).")";
			$db->Execute($query);
		}

		return $db->CompleteTrans();

	}

	public static function setUsers($role, $users, &$errors=array())
	{

		if(!$role instanceof Role)
		{
			$roleid=$role;
			$role = new Role();
			$role=$role->load($roleid);
			if($role===false)
			{
				return false;
			}
		}

		$db=DB::Instance();
		$db->StartTrans();

		$query="delete from hasrole where roleid=".$db->qstr($role->id);
		$db->Execute($query);

		foreach($users as $user)
		{
			$ob = DataObject::Factory(array('roleid'=>$role->id, 'username'=>$user), $errors, 'HasRole');
			if (count($errors)==0 && $ob)
			{
				$ob->save();
			}
		}

		$db->CompleteTrans();

	}

	public function getPermissions()
	{
		$permission = new HasPermission();
		$permission->identifierField = 'permissionsid';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('roleid', '=', $this->{$this->idField}));
		return $permission->getAll($cc);
	}

	public function getReports()
	{
		$report = new HasReport();
		$report->identifierField = 'description';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('role_id', '=', $this->{$this->idField}));
		return $report->getAll($cc, null, TRUE);
	}

	public function getRoles()
	{
		$role = new HasRole();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('roleid', '=', $this->{$this->idField}));
		return $role->getAll($cc);
	}

	public function getUsers()
	{
		$report = new HasRole();
		$report->identifierField = 'username';
		$cc = new ConstraintChain();
		$cc->add(new Constraint('roleid', '=', $this->{$this->idField}));
		return $report->getAll($cc);
	}

}

// End of Role

