<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AccessObject {

	protected $version='$Revision: 1.42 $';
	
	public $id;

	// $permissions - array of all user's permissions
	// key is permission_id
	// contains array of permission data
	public $permissions = array();

	//	public $companyPermissions;
	
	// $tree - multi dimensional array of user's permissions as a tree
	// key is permission_id
	// contains array of permission data with next level in array of ['children'] key
	public $tree = array();

	// $roles - single dimension array of user's roles
	// key is role_id
	// value is role_id
	public $roles = array();

	protected function __construct($username)
	{
		
		if (!defined('EGS_COMPANY_ID'))
		{
			return false;
		}
		
		if (empty($username))
		{
			return false;
		}
		
		$this->id = $username;
		
		if (!$this->load() || isset($_GET['companyselector']))
		{
			$this->setPermissions();	
		}
		
		/*
		foreach ($this->roles as $key=>$value) {
			echo 'Roles '.$key.'='.$value.'<br>';
		}
		foreach ($this->companyPermissions as $key=>$value) {
			echo 'Company Permissions '.$key.'='.$value.'<br>';
		}
		foreach ($this->permissions as $key=>$value) {
			echo 'Permissions '.$key.'='.$value.'<br>';
		}
		*/
		
	}

	public static function &Instance($username = null)
	{
		
		static $accessobject;
		
		if ($accessobject == null)
		{
			$accessobject = new AccessObject($username);		
		}
		
		return $accessobject;
		
	}

	public function save()
	{
		$_SESSION['permissions'] = serialize($this);
	}

	public function load()
	{
		
		if (!isset($_SESSION['permissions']))
		{
			return false;
		}
		
		$access = unserialize($_SESSION['permissions']);
		
		if ($this->id !== $access->id)
		{
			return false;
		}

		$this->permissions	= $access->permissions;
		$this->tree			= $access->tree;
		$this->roles		= $access->roles;

//		echo 'permissions<pre>'.print_r($this->permissions, true).'</pre><br>';		
//		$this->companyPermissions = $access->companyPermissions;

		return true;

	}
	
	/*
	 * 
	 */
	public function can_manage_uzlets ()
	{
		$role = DataObjectFactory::Factory('Role');
		
		if (!empty($this->roles))
		{
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('id', 'IN', '(' . implode(',', $this->roles) . ')'));
			
			$cc->add(new Constraint('manage_uzlets', 'IS', 'true'));
			
			return ($role->getCount($cc) > 0);
		}
		return FALSE;
	}
	
	/**
	 * Sets the list of permissions from the database
	 * 
	 * @return	boolean	if the function correctly gets the list of permissions it returns true else false
 	 *
	 * @todo	this function
	 */
	public function setPermissions()
	{

		$super				= false;
		$permission			= DataObjectFactory::Factory('Permission');
		$permission_types	= $permission->getEnumOptions('type');
		
		// Get the roles the user is assigned to for this company
		$this->setRoles();
		
		// someone with no roles can't have access to anything
		if (empty($this->roles))
		{
			return false;
		}
		
		// get the permissions (ids) for the roles the user has access to
		// constrained by the permissions the company has access to
		// Problem here is that company permissions are related to modules
		// but user permissions are related to modules, controllers, actions
		
		$hp					= DataObjectFactory::Factory('HasPermission');
		$role_permissions	= $hp->getPermissionID(null, $this->roles);

		// TODO: A user may have access to more than one company, but we may want
		// assign different permissions to the user for the different companies
		// Company permissions currently disabled
				
		//get the permissions data for the permissions the user has access to
		$user_permissions = new PermissionCollection();
		
		if (!empty($role_permissions))
		{
			$data = $user_permissions->getPermissions($role_permissions);
		}
		
		$permissions = array();
		
		if (count($data) > 0)
		{
			
			foreach($data as $permission)
			{
				$permissions[$permission['id']] = $permission;
			}
			
		}
		
		$this->permissions = $permissions;

		//we need to make sure that roles haven't been given access to things that the company doesn't have access to
		/*
				$company_permissions=new CompanypermissionCollection();
				$company_permissions->getPermissions(EGS_COMPANY_ID, 'position');
				$this->permissions=array();
				
				foreach($company_permissions as $permission) {
					if(isset($mod_permissions[$permission->permission])) {
						$this->permissions[$permission->permission]=$mod_permissions[$permission->permission];
					}
				}
				$this->permissions=$this->permissions+$permissions;
				$this->tree = $this->getPermissionTree($this->permissions);
		*/
				
		// Build the Permissions tree for the user's menu access
		$this->tree = array();
		
		foreach ($permissions as $permission)
		{
			
			if ($permission['display'] == 't'
				&& (empty($permission['parent_id']) || (!empty($permission['parent_id']) && $permissions[$permission['parent_id']]['display']=='t')))
			{
				
				// set a starting value for older style permissions
				$link_params = array(
					strtolower($permission_types[$permission['type']]) => $permission['permission']
				);
								
				// we can work out the correct link if the permission has a module_id
				if (!empty($permission['module_id']))
				{
					
					$module		= DataObjectFactory::Factory('ModuleObject');
					$controller	= DataObjectFactory::Factory('ModuleComponent');
					
					switch ($permission['type']) 
					{
						
						case 'a':
							$link_params['action']		= $permission['permission'];
							$link_params['controller']	= str_replace('controller', '', $controller->load_identifier_value($permission['component_id']));
							$link_params['module']		= $module->load_identifier_value($permission['module_id']);
							break;
							
						case 'c':
							$link_params['controller']	= str_replace('controller', '', $controller->load_identifier_value($permission['component_id']));
							$link_params['module']		= $module->load_identifier_value($permission['module_id']);
							break;
						
						case 'm':
							$link_params['module']		= $module->load_identifier_value($permission['module_id']);
							$moduleHasUzlets = $module->hasUzlets();
							break;
						
					}
					
				}
				
			 	if ($permission['has_parameters'] == 't')
			 	{
			 		
				 	$permission_parameters			= DataObjectFactory::Factory('PermissionParameters');
				 	$permission_parameters->idField	= 'name';
					
					$cc = new ConstraintChain();
					$cc->add(new Constraint('permissionsid', '=', $permission['id']));
					
					$link_params += $permission_parameters->getAll($cc);
					
			 	}
			 	
			 	$key = (!isset($permission['parent_id']))?0:$permission['parent_id'];
			 	
				$this->tree[$key][$permission['position']] = array(
					'id'	=> $permission['id'],
					'title'	=> $permission['title'],
					'type'	=> $permission['type'],
					'link'	=> $link_params
				);

				if (isset($moduleHasUzlets)) {
					$this->tree[$key][$permission['position']]['has_uzlets'] = $moduleHasUzlets;
				}
				
				$this->tree[$key][$permission['position']]['new_type_permission'] = (!empty($permission['module_id']));
				
			}
			
		}
		
		foreach ($this->tree as $treekey => $treenode)
		{
			
			if ($treekey == 0)
			{
				
				foreach ($treenode as $position => $permission)
				{
					$this->buildTree($permission);
				}
				
			}
			
		}
		
		ksort($this->tree);
		
		foreach ($this->tree as $treekey => $position)
		{
			ksort($position);
			$this->tree[$treekey] = $position;
		}
		
		return true;
		
	}

	private function buildTree($parent)
	{
		
		foreach ($this->tree as $treekey => $treenode)
		{
			
			if ($treekey == $parent['id'])
			{
				
				foreach ($treenode as $position => $permission)
				{
					
					if ($permission['new_type_permission'] !== TRUE)
					{
						
						switch ($permission['type'])
						{
							
							case 'm':
								
								if (isset($parent['module']))
								{
									$this->tree[$treekey][$position]['link']['module'] = $parent['link']['module'] . $this->tree[$treekey][$position]['link']['module'];
								}
								else
								{
									$this->tree[$treekey][$position]['link']['module'] = $this->tree[$treekey][$position]['link']['module'];
								}
								
								$permission['link']['module'] = $this->tree[$treekey][$position]['link']['module'];
								break;
								
							case 'a':
								$this->tree[$treekey][$position]['link']['controller'] = $parent['link']['controller'];
								$permission['link']['controller'] = $this->tree[$treekey][$position]['link']['controller'];
								
							case 'c':
								$this->tree[$treekey][$position]['link']['module'] = $parent['link']['module'];
								$permission['link']['module'] = $this->tree[$treekey][$position]['link']['module'];
								break;
						}
					
					}
					
					$this->buildTree($permission);
										
				}
				
			}
			
		}
		
		return;
		
	}
	
	/**
	 * Get the list of permissions from the database
	 * 
	 * @return	boolean	if the function correctly gets the list of permissions it returns true else false
 	 *
	 * @todo	this function
	 */
	public function setRoles()
	{

		$this->roles	= array();
		$hasrole		= DataObjectFactory::Factory('HasRole');
		$results		= $hasrole->getRoleID($this->id);
		
		if (!$results)
		{
			return false;
		}
		
		$this->roles = $results;
		
		return true;

	}

	/**
	 * Check in the list of permission to see if this user has access to the requested action
	 * 
	 * @param	string	the name of the action to be checked
	 * @param	string	the controller name
	 * @param	string	the module name
	 * @return	boolean	if has permission return true else return false
 	 *
	 */
	public function hasPermission($modules, $controller = '', $action = '', $pid = '')
	{
		
		if (!is_array($modules))
		{
			$modules = array('module' => $modules);
		}
		
		//echo 'AccessObject:hasPermission modules='.implode('=',$modules).' controller='.$controller.' action='.$action.' pid='.$pid.'<br>';
		debug('AccessObject::hasPermission modules ' . implode('=', $modules).' : controller '.$controller.' : action '.$action);
		
		$controller = str_replace('controller', '', strtolower($controller));
		
		if ($modules['module'] =='dashboard' && (empty($controller) || $controller == 'index'))
		{
			return true;
		}
		
		if ($modules['module'] =='login' || trim($modules['module']) == '')
		{
			return true;
		}
		
//		if($this->check('egs')) {
//			return true;
//		}

		$action = strtolower($action);

		if ($this->getCache($modules, $controller, $action))
		{
			return true;
		}
		
		if (isset($pid) && !isset($this->permissions[$pid]))
		{
			
			$permission = DataObjectFactory::Factory('Permission');
			$permission->load($pid);
			
			if ($permission->isLoaded())
			{
				// permission exists but user does not have access to it
				return false;
			}
			
		}
		
		if ($action == 'new')
		{
			$action = '_new';
		}
		
		if (isset($pid) && isset($this->permissions[$pid]))
		{
			
			switch ($this->permissions[$pid]['type'])
			{
				
				case 'g':
				case 'm':
					
					if (!in_array($this->permissions[$pid]['permission'], $modules))
					{
						return false;
					}
					break;
					
				case 'c':
					if ($this->permissions[$pid]['permission'] != $controller)
					{
						return false;
					}
					break;
					
				case 'a':
					
					if ($this->permissions[$pid]['permission'] != $action)
					{
						return false;
					}
					break;
					
			}
			
			return true;
			
		}
		
		// TODO : Need to check down the modules tree and 
		$permissions = new PermissionCollection();
		$module_permissions = $permissions->checkPermission($modules, array('g', 'm'));
		
		if (count($module_permissions) == 0)
		{
			//	module does not exist in permissions so user cannot have access to it
			return false;
		}
		
		$parent_ids = array();
		
		foreach ($module_permissions as $permission)
		{
			
			if (isset($this->permissions[$permission['id']]))
			{
				$parent_ids[] = $permission['id'];
			}
			
		}
		
		if (empty($parent_ids))
		{
			// module exists in permissions but user does not have access to it
			return false;
		}
		
		// Need to use default controller if controller is empty?
		if ($controller !== '')
		{
			
			// echo 'AccessObject:hasPermission checking controller '.$controller.'<br>';
			
			$permissions = new PermissionCollection();
			$controller_permissions = $permissions->checkPermission($controller, 'c', $parent_ids);
			
			if (count($controller_permissions) == 0)
			{
				//	controller does not exist in permissions so user has access to it by default
				return true;
			}
			
			$parent_ids = array();
			
			foreach ($controller_permissions as $permission)
			{
				
				if (isset($this->permissions[$permission['id']]))
				{
					$parent_ids[] = $permission['id'];
				}
				
			}
			
			if (empty($parent_ids))
			{
				// controller exists in permissions but user does not have access to it
				return false;
			}
			
		}
		
		// Need to use default action if action is empty?
		if ($action !== '')
		{
			
			// echo 'AccessObject:hasPermission checking action '.$action.'<br>';
			
			$permissions = new PermissionCollection();
			$action_permissions = $permissions->checkPermission($action, 'a', $parent_ids);
			
			if (count($action_permissions) == 0)
			{
				//	action does not exist in permissions so user has access to it by default
				return true;
			}
			
			$parent_ids = array();
			
			foreach ($action_permissions as $permission)
			{
				
				if (isset($this->permissions[$permission['id']]))
				{
					$parent_ids[] = $permission['id'];
				}
				
			}
			
			if (empty($parent_ids))
			{
				// action exists in permissions but user does not have access to it
				return false;
			}
			
		}
		
		return true;
		
	}

	function getPermission($module = NULL, $controller = NULL, $action = NULL, $pid = NULL)
	{

		$permission_id	= NULL;
		$check_type		= 'm';

//		$cache = $this->getCache($module, $controller, $action);
//		if ($cache)
//		{
//			return $cache;
//		}
		
		
		if (!empty($pid))
		{
			
			$permission = DataObjectFactory::Factory('Permission');
			$permission->load($pid);
			
			if ($permission->isLoaded())
			{
				
				$check_type = strtolower($permission->type);
				
				if ($check_type == 'a')
				{
					return $pid;
				}
				
				$parent_ids = array($pid);
				
				if ($check_type=='c')
				{
					// Need to check if permissions exist on action
					$check_type = 'a';
				}
				else
				{
					// Need to check if permissions exist on controller
					$check_type = 'c';
				}
				
			}
			else
			{
				// pid supplied but it doesn't exist in permissions - something wrong here!
				return null;
			}
			
		}
		
		if (empty($module))
		{
			return null;	
		}
		
		if ($check_type=='m')
		{
			
			$permission = DataObjectFactory::Factory('Permission');
			$module_permissions = $permission->getPermissions($module, array('g', 'm'));
		
			if (count($module_permissions) == 0)
			{
				//	module does not exist in permissions so return null pid
				return null;
			}
			
			$parent_ids = array_keys($module_permissions);
			
			$check_type = 'c';
			
		}
		
		if (empty($controller))
		{
			
			if (!empty($parent_ids))
			{
				$permission_id = $parent_ids[0];
			}
			
//			$this->saveCache($module, $controller, $action, $permission_id);
		
			return $permission_id;
			
		}
		else
		{
			
			$controller = strtolower($controller);
			
			if ($check_type == 'c')
			{
				
				$permission = DataObjectFactory::Factory('Permission');
				
				$controller_permissions = $permission->getPermissions($controller, 'c', $parent_ids);
				
				if (count($controller_permissions) == 0)
				{

					//	controller does not exist in permissions so return parent module pid
					if (!empty($parent_ids))
					{
						$permission_id = $parent_ids[0];
					}
					
//					$this->saveCache($module, $controller, $action, $permission_id);
		
					return $permission_id;
					
				}
				
				$parent_ids = array_keys($controller_permissions);
				
				$check_type = 'a';
				
			}
			
		}
		
		if (empty($action))
		{
			
			if (!empty($parent_ids))
			{
				$permission_id = $parent_ids[0];
			}
			
//			$this->saveCache($module, $controller, $action, $permission_id);
		
			return $permission_id;
			
		}
		else
		{
			
			if ($action == 'new')
			{
				$action = '_new';
			}
			
			$action = strtolower($action);
			
			if ($check_type == 'a')
			{
				
				$permissions = DataObjectFactory::Factory('Permission');
				$action_permissions = $permissions->getPermissions($action, 'a', $parent_ids);
				
				if (count($action_permissions) == 0)
				{

					//	action does not exist in permissions so return parent controller pid
					if (!empty($parent_ids))
					{
						$permission_id = $parent_ids[0];
					}
					
//					$this->saveCache($module, $controller, $action, $permission_id);
		
					return $permission_id;
					
				}
				
				$parent_ids= array_keys($action_permissions);
								
			}
			
		}
		
		if (!empty($parent_ids))
		{
			$permission_id = $parent_ids[0];
		}
		
//		$this->saveCache($module, $controller, $action, $permission_id);
		
		return $permission_id;
		
	}
	

	public function getParentPermission($pid, $modules, $controller = '', $action = '')
	{
		if (!is_array($modules))
		{
			$modules = array($modules);
		}
		
		switch ($this->permissions[$pid]['type'])
		{
			case 'm':
				foreach ($modules as $module)
				{
					if ($module == $this->permissions[$pid]['permission'])
					{
						return $this->permissions[$pid]['parent_id'];
					}
				}
				break;
			case 'c':
				if (strtolower($controller) == $this->permissions[$pid]['permission'])
				{
					return $this->permissions[$pid]['parent_id'];
				}
				break;
			case 'a':
				if (strtolower($action) == $this->permissions[$pid]['permission'])
				{
					return $this->permissions[$pid]['parent_id'];
				}
				break;
		}
		
		if (count($this->tree[$pid]) > 0)
		{
			// Shouldn't be here - error
			return false;
		}
		
		// This is the parent
		return $pid;
		
	}
	
	public function saveCache ($module, $controller = '', $action = '', $pid = '')
	{
		if (is_array($module))
		{
			$module = current($module);
		}
		
		$controller = strtolower($controller);
		
		if (!empty($pid))
		{
			$_SESSION['cache']['link'][$module][$controller][$action ]['pid'] = $pid;
		}
		elseif (isset($_SESSION['cache']['link'][$module][$controller][$action ]['pid']))
		{
			unset($_SESSION['cache']['link'][$module][$controller][$action ]['pid']);
		}
		
	}
	
	public function getCache ($module, $controller = '', $action = '')
	{
		if (is_array($module))
		{
			$module = current($module);
		}
		
		$controller = strtolower($controller);
		
		if (isset($_SESSION['cache']['link'][$module][$controller][$action ]['pid']))
		{
			return $_SESSION['cache']['link'][$module][$controller][$action ]['pid'];
		}
		
		return false;
	
	}
	
	public function getDefaultModule()
	{
		return $this->tree[0][1]['link']['module'];
	}
	
	public function setHelpContext($pid = '')
	{
		if (empty($pid))
		{
			return '';
		}
		
		$permission = DataObjectFactory::Factory('Permission');
		$permission->load($pid);
		
		$help_link = '';
		
		if (!is_null($permission->component_id))
		{
			$help_link = $permission->component->help_link;
		}
		
		if (!is_null($permission->module_id) && empty($help_link))
		{
			$help_link = $permission->module->help_link;
		}
		
		return $help_link;
	}
	
	/*
	 * 
	 */
	public function getUserModules($username = EGS_USERNAME, $cc = null)
	{
		// Get the roles for the user
		$hasrole	= DataObjectFactory::Factory('HasRole');
		$roles		= $hasrole->getRoleID($username);
		
		$hp			= new HasPermissionCollection;
				
		if (!($cc instanceof ConstraintChain))
		{
			$cc = new ConstraintChain();
		}
		
		// Only interested in top level permissions
		$cc->add(new Constraint('type', 'in', "('g', 'm')"));
		
		if (!empty($roles))
		{
			$cc->add(new Constraint('roleid', 'in', '(' . implode(',', $roles) . ')'));
		}
		
		$sh = new SearchHandler($hp, FALSE);
		
		$sh->addConstraintChain($cc);
		
		return $hp->load($sh, null, RETURN_ROWS);
		
	}
//********************************************************************************
//
//  Obsolete ???

	public function checkCompanyPermission($module)
	{

		if (empty($this->companyPermissions))
		{
			return true;
		}
		
		foreach ($this->companyPermissions as $key => $comp) 
		{

			if ($comp['permission'] == $module)
			{
				return true;
			}
			
		}
		
		return false;
		
	}

	/**
	 * @todo	actually check
	 *
	 */
	public function companyPermission($modules)
	{
		
		if (empty($modules))
		{
			return false;
		}
		
		if (count($modules) > 1)
		{
			
			$check = array_shift($modules);
			
			if ($this->checkCompanyPermission($check))
			{
				return true;
			}
			else
			{
				
				foreach ($modules as $module)
				{
					
					$check = $check . '-'. $module;
					
					if ($this->checkCompanyPermission($check))
					{
						return true;
					}
					
				}
				
			}
			
		}
		else
		{
			$check = strtolower($modules[0]);
		}
		
		if ($this->checkCompanyPermission($check))
		{
			return true;
		}
		
		return false;
		
	}

	function getPermissionTree($result)
	{
						
		$permissionTree	= new PermissionTree();
		$tree			= array();
		
		if (!isset($result) || empty($result))
		{
			return false;
		}
		
		foreach ($result as $permission)
		{	
			$explode	= explode('-', $permission['permission']);
			$tree		= $permissionTree->makeTree($tree, $explode, $permission);
		}
		
		usort($tree, array('PermissionTree', 'compare'));
		
		return $tree;
		
	}

	//a shortcut for looping over modules checking permission
	//returns true if the user has access to _any_ of the modules
	
	public function hasPermissionAny($modules)
	{
		
		if (!is_array($modules))
		{
			return true;
		}
		
		foreach ($modules as $module)
		{
			
			if ($this->hasPermission($module))
			{
				debug('AccessObject::hasPermissionAny module ' . $module . ' - access allowed');
				return true;
			}
			
			debug('AccessObject::hasPermissionAny module ' . $module . ' - access denied');
			
		}
		
		return false;
		
	}

	//returns true iff the user has access to _all_ of the modules
	public function hasPermissionAll($modules)
	{
		
		foreach ($modules as $module)
		{
			
			if (!$this->hasPermission($module))
			{
				return false;
			}
			
		}
		
		return true;
		
	}

}

// end of AccessObject.php