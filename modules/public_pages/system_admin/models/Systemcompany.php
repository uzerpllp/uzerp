<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Systemcompany extends DataObject {

	protected $version = '$Revision: 1.20 $';
	
	protected $defaultDisplayFields = array('company'=>'Company'
										   ,'access_enabled'=>'Access'
										   ,'theme'=>'Theme'
										   ,'audit_enabled'=>'Audit'
										   ,'debug_enabled'=>'Debug'
										   ,'published'=>'Published'
										   ,'published_owner'=>'Administrator');

	function __construct()
	{
		parent::__construct('system_companies');
		
		$this->idField='id';
		$this->identifierField='company';
		
		$this->validateUniquenessOf('company_id');
		
 		$this->belongsTo('Company', 'company_id', 'company'); 
		$this->belongsTo('Person', 'published_owner_id', 'published_owner');
		 
		$this->setEnum('access_enabled',array('FULL'=>'Full'
											 ,'RESTRICTED'=>'Restricted'
											 ,'NONE'=>'None'));
		
		$this->hasMany('Usercompanyaccess', 'users', 'usercompanyid');
		$this->hasMany('Role', 'roles', 'usercompanyid');
		$this->hasOne('Company', 'company_id', 'systemcompany');
		
		$sh = new SearchHandler(new UsercompanyaccessCollection(DataObjectFactory::Factory('Usercompanyaccess')), false, false);
		$this->addSearchHandler('users', $sh);
		
		$this->getField('theme')->setDefault('default');
		$this->getField('access_enabled')->setDefault('FULL');
	}

	public function publish (&$errors)
	{
		$name = "abc";
		
		$format = new xmlrpcmsg('elgg.user.newUser',array(new xmlrpcval($name, "string")));
		
		$client = new xmlrpc_client("_rpc/RPC2.php", "tech2.severndelta.co.uk", 8091);
		
		$request = $client->send($format);
		
		$value = $request->value();
		
		if (!$request->faultCode())
		{
			$this->published=true;
			$this->admin_owner=$request->serialize();
		}
		else
		{
			$errors[]="Code: ".$request->faultCode()." Reason '".$request->faultString();
			return false;	
		}
		
		return true;	
	}
	
	/**
	 * @param $permissions array
	 * @return boolean
	 *
	 * Takes an array of $permission_id=>'on' pairs and adds permissions for the company
	 */
	public function setPermissions($permissions, &$errors=array())
	{
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$result=true;
		
//		Save the Company Permissions

		$companypermission = DataObjectFactory::Factory('Companypermission');
		
		$delete_permissions = $companypermission->getAll();
		
		$current_permissions = count($delete_permissions);

		if (empty($delete_permissions))
		{
			foreach ($permissions as $permission_id=>$on)
			{
				$delete_permissions[]=$permission_id;
			}
		}
//		1) if item is selected, do not delete it [unset($delete_permissions[$id])]
//		2) if item is selected and it already exists in Company Permissions [$current_permissions>0]
//			we do not need to insert it [unset($permissions[$permissionid])]
		$deleteHasPermissions = false;
		
		foreach ($delete_permissions as $id=>$permissionid)
		{
			if (isset($permissions[$permissionid]['permissions']))
			{
				unset($delete_permissions[$id]);
				
				if ($current_permissions>0)
				{
					unset($permissions[$permissionid]);
				}
				
				$deleteHasPermissions = true;
			}
		}

//		delete the company permissions where the input is deselected
		if (!empty($delete_permissions))
		{
			$companypermissions = new CompanypermissionCollection($companypermission);
			
			$sh = new SearchHandler($companypermissions, false, false);
			
			if (!empty($delete_permissions))
			{
				$sh->addConstraint(new Constraint('permissionid', 'in', '('.implode(',', $delete_permissions).')'));
			}
			
			$companypermissions->delete($sh);
		}
		
		foreach ($permissions as $permission_id=>$on)
		{
			if (isset($on['permissions']))
			{
				$data['permissionid'] = $permission_id;
				
				$cp=DataObject::Factory($data, $errors, 'Companypermission');
				
				if (count($errors)>0 || !$cp->save())
				{
					$errors[]='Failed to save Company Permissions';
					$db->FailTrans();
					
					$result = false;
					break;
				}
			}
		}
		
//		only delete role permissions for deselected lines
//      if not all lines deselected
		if (count($errors)==0 && $deleteHasPermissions)
		{
			foreach ($delete_permissions as $permission_id)
			{
				$this->deleteHasPermissions($permission_id, $errors);
			}
		}
		
		$db->CompleteTrans();
		
		return $result;
	}
	
	/**
	 * Saving a new Systemcompany causes:
	 * - a default admin role to be added. 
	 * - the admin role is given access to all the modules of the company
	 * - the creating-user is given access to the systemcompany
	 * -  the creating-user is put in the admin role
	 *
	 * @return boolean
	 */
	public function save($debug=false)
	{
		$db=DB::Instance();
		
		$db->StartTrans();
		
		$result = parent::save($debug);
		
		if($result===false)
		{
			$db->FailTrans();
			return ($db->CompleteTrans());
		}
		
		//if it succeeded, then see if we need to make a default role and give the creating user access
		$query = 'SELECT count(*) FROM roles WHERE usercompanyid='.$db->qstr($this->id);
		
		$num_roles = $db->GetOne($query);
		
		if($num_roles>0)
		{
			return ($db->CompleteTrans());	//not a fail, just no need to continue
		}
		
		$errors=array();
		
		$role_data = array();
		$role_data['description']	= 'A default role for Admin users, has access to all modules';
		$role_data['name']			= 'Admin';
		$role_data['usercompanyid']	= $this->id;
		
		$admin_role = DataObject::Factory($role_data,$errors,'Role');
		
		if($admin_role===false||$admin_role->save()===false)
		{
			$db->FailTrans();
			return false;
		}
		
		$has_role_data=array(
			'roleid'=>$admin_role->id,
			'username'=>EGS_USERNAME
		);
		
		$has_role = DataObject::Factory($has_role_data,$errors,'HasRole');
		
		if($has_role===false||$has_role->save()===false)
		{
			$db->FailTrans();
			return false;
		}
		
		$uca_data = array(
			'username'=>EGS_USERNAME,
			'usercompanyid'=>$this->id,
			'enabled'=>true
		);
		
		$uca = DataObject::Factory($uca_data,$errors,'Usercompanyaccess');
		
		if($uca===false||$uca->save()===false)
		{
			$db->FailTrans();
			return false;
		}
		
		$company = DataObjectFactory::Factory('Company');
		
		$company->update($this->company_id, 'usercompanyid', $this->id );
		
		return ( $db->CompleteTrans() );
	}
	
	public static function countNonUsers()
	{
		$db = DB::Instance();
		
		$query = 'SELECT id FROM person p LEFT JOIN users u ON (p.id=u.person_id) WHERE p.usercompanyid='.EGS_COMPANY_ID.' LIMIT 1';
		
		$count = $db->GetOne($query);
		
		return $count;
	}

	public function getVRN()
	{
		$vrn = preg_replace("/[^0-9]/", "", $this->systemcompany->vatnumber);
		return $vrn;
	}

	function getNonUsers ()
	{
//		$users=array();
//		foreach ($this->users as $user) {
//			$users[]=$user->username;
//		}
		$db = DB::Instance();
		
		$user = DataObjectFactory::Factory('User');
		
		$users = $user->getAll();
		
		if ($users)
		{
			foreach ($users as $key=>$user)
			{
				$users[$key] = $db->qstr($user);
			}
		}
		else
		{
			$users = array();
		}
		
		$userlist=implode(',',$users);
		
		$nonusers = DataObjectFactory::Factory('User');
		
		$cc = new ConstraintChain();
		
		if (!empty($userlist))
		{
			$cc->add(New Constraint('username', 'not in', '('.$userlist.')'));
		}
		
		return $nonusers->getAllUsers($cc);
	}
	
	function getCurrentPeople()
	{
		$people = DataObjectFactory::Factory('Person');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company_id', '=', $this->company_id));
		
		$people->getCurrent($cc1);
		
		return $people->getAll($cc, TRUE);
		
	}
	
	function getCompanyAddress ()
	{
		$companyAddress = DataObjectFactory::Factory('Companyaddress');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('party_id', '=', $this->systemcompany->party_id));
		$cc->add(new Constraint('main', '=', true));
		
		$companyAddress->loadBy($cc);
		
		return $companyAddress;
	}

	/**
	 * Get the reply email address to be used when sending customer statements
	 *
	 * @return string  Email address
	 */
	function getStatementReplyToEmailAddress() {
		$companyContact = DataObjectFactory::Factory('PartyContactMethod');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('party_id', '=', $this->systemcompany->party_id));
		$cc->add(new Constraint('lower(name)', '=', 'statement'));
		$cc->add(new Constraint('type', '=', 'E'));

		if (!$companyContact->loadBy($cc)) {
			return false;
		}
		return $companyContact->contact;
	}

	function companyName ()
	{
		$company = DataObjectFactory::Factory('Company');
		
		$company->load($this->company_id, true);
		
		return $company->{$company->getIdentifier()};
	}
	
	public function get_logo ($_logo_path = '')
	{

		if (!is_null($this->logo_file_id))
		{
			$file = DataObjectFactory::Factory('File');
			$file->load($this->logo_file_id);
			
			$file->path = DATA_USERS_URL . 'tmp';
			$image = $file->pull();
			
			return $image['path'].$image['filename'];
		}
	
		return $_logo_path;
	}
	
	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}
	
	/*
	 * Private Functions
	 */
	private function deleteHasPermissions ($permission_id, &$errors=array())
	{
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$hp = new HasPermissionCollection(DataObjectFactory::Factory('HasPermission'), 'haspermission');
		
		$sh = new SearchHandler($hp, false);
		
		$sh->addConstraint(new Constraint('permissionsid', '=', $permission_id));
		
		$hp->delete($sh);
		
 		$permission = DataObjectFactory::Factory('Permission');
 		
		$permission->load($permission_id);
		
		if ($permission && $permission->sub_permissions->count()>0)
		{
			foreach ($permission->sub_permissions as $sub_permission)
			{
				$this->deleteHasPermissions($sub_permission->id, $errors);
			}	
		}
		return $db->CompleteTrans();
	}
	
	private function setHasPermissions ($permission_id, $data, &$errors=array())
	{
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$data['permissionsid']=$permission_id;
		
		$hp = DataObject::Factory($data, $errors, 'HasPermission');
		
		if (count($errors)>0 || !$hp->save())
		{
			$errors[]='Failed to assign permissions to roles';
			$db->FailTrans();
		}
		else
		{
			$permission = DataObjectFactory::Factory('Permission');
			
			$permission->load($permission_id);
			
			if ($permission && $permission->sub_permissions->count()>0)
			{
				foreach ($permission->sub_permissions as $sub_permission)
				{
					$this->setHasPermissions($sub_permission->id, $data, $errors);
					
					if (count($errors)>0)
					{
						$errors[]='Failed to assign permissions to roles';
						$db->FailTrans();
						break;
					}
				}	
			}
		}
		
		return $db->CompleteTrans();
	}
	
}

// End of Systemcompany
