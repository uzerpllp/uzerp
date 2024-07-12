<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Permission extends DataObject {

	protected $version = '$Revision: 1.20 $';
	
	function __construct($tablename = 'permissions')
	{

		$this->defaultDisplayFields = array(
			'permission'	=> 'permission',
			'description'	=> 'description'
		);
		
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		
		$this->actsAsTree('parent_id');
		
		$this->setEnum(
			'type',
			array(
				'a'	=> 'Action',
				'c'	=> 'Controller',
				'g'	=> 'Group',
				'm'	=> 'Module',
				's'	=> 'Sub Module',
				'x'	=> 'Custom'
			)
		);

		$this->orderby			= 'position';
		$this->identifierField	= 'permission';
		
 		$this->hasOne('Permission', 'parent_id', 'parent');
 		$this->hasOne('ModuleObject', 'module_id', 'module');
 		$this->hasOne('ModuleComponent', 'component_id', 'component');
 		
 		 $this->hasMany('HasPermission', 'roles', 'permissionsid');
		$this->hasMany('CompanyPermission', 'companies', 'permissionsid');
		$this->hasMany('Permission', 'sub_permissions', 'parent_id');
		$this->hasMany('PermissionParameters', 'parameters', 'id');
		
	}

	function hasRole($roleid)
	{
		
		foreach ($this->roles as $hasrole)
		{
			
			if ($roleid == $hasrole->roleid)
			{
				return TRUE;	
			}
			
		}
		
		return FALSE;
		
	}
	
	function getMenuTree($types = array(), $name = NULL)
	{
		
		$menutree = new DOMDocument();
		
		if (!empty($types) && !is_array($types))
		{
			$types = array($types);
		}
		
		if (!empty($types))
		{
			$types = implode(',', $types);
		}
		
		$this->getPermissionsAsTree(NULL, $menutree, $menutree, $types, $name);
		
		return $menutree;
		
	}

	
	function getPermissionsAsTree($id = NULL, $parent = null, $sitetree = null, $types = NULL, $name = NULL)
	{
		
		$db = DB::Instance();
		$cc = new ConstraintChain();
		
		if (empty($id))
		{
			$cc->add(new Constraint('parent_id', 'is', 'NULL'));
		}
		else
		{
			$cc->add(new Constraint('parent_id', '=', $id));
		}
		
		if (!empty($types))
		{
			$cc->add(new Constraint('type', 'in', '('.$types.')'));
		}
		
		if (!empty($name))
		{
			$cc->add(new Constraint('id', '=', $name));
		}
		
		$this->identifierField	= 'title';
		$this->orderby			= 'position';
		
		$thislevel = $this->getAll($cc);
		
		if ($thislevel)
		{
			
			foreach ($thislevel as $pageid=>$name)
			{
				
				$element = $sitetree->createElement('Permission',$name);
				$element->setAttribute('id',$pageid);
				
				$this->getPermissionsAsTree($pageid,$element,$sitetree,$types);
				
				$parent->appendChild($element);
				
			}
			
		}
		else
		{
			return FALSE;
		}
		
	}

	function getIcon($type, $name)
	{
		
		if (file_exists(FILE_ROOT . 'assets/graphics/' . $name . '_small.png'))
		{
			return '/assets/graphics/' . $name . '_small.png';
		}
		else
		{
			
			$types		= $this->getEnumOptions('type');
			$type_name	= strtolower($types[$type]);
			
			if (file_exists(FILE_ROOT . 'assets/graphics/' . $type_name . 's_small.png'))
			{
				return '/assets/graphics/' . $type_name . 's_small.png';
			}
			
		}
		
		return NULL;
		
	}

	function visiblePermissions()
	{
		
		foreach ($this->sub_permissions as $permission)
		{
			
			if ($permission->display == 't')
			{
				return TRUE;
			}
			
		}
		
		return FALSE;
		
	}

	static function getNextPosition($parent_id = NULL)
	{
		
//		$permission	= new Permission();
		$permission	= DataObjectFactory::Factory('Permission');
		$sh			= new SearchHandler(new PermissionCollection($permission), FALSE);
		
		if (is_null($parent_id) || empty($parent_id))
		{
			$sh->addConstraint(new Constraint('parent_id', 'is', 'NULL'));	
		}
		else
		{
			$sh->addConstraint(new Constraint('parent_id', '=', $parent_id));	
		}
		
		$sh->addConstraint(new Constraint('position', 'is not', 'NULL'));	
		$sh->setOrderby('position', 'DESC');
		
		if ($permission->loadBy($sh))
		{
			return $permission->position;
		}
		else
		{
			return FALSE;
		}
		
	}

	static function exists($data)
	{
		
//		$permission	= new Permission();
		$permission	= DataObjectFactory::Factory('Permission');
		$cc			= new ConstraintChain();
		
		$cc->add(new Constraint('permission', '=', $data['permission']));
		
		if (empty($data['parent_id'])) {
			$cc->add(new Constraint('parent_id', 'is', 'NULL'));
		}
		else
		{
			$cc->add(new Constraint('parent_id', '=', $data['parent_id']));
		}
		
		$permission->loadBy($cc);
		
		return $permission;
		
	}
	
	function build_link($pid)
	{
		
//		$module		= new ModuleObject();
//		$controller	= new ModuleComponent();
		
//		$permission = new Permission();

		$module		= DataObjectFactory::Factory('ModuleObject');
		$controller	= DataObjectFactory::Factory('ModuleComponent');		
		
		$permission = DataObjectFactory::Factory('Permission');
		$permission->load($pid);
		
		$link = array();
		
		if (!$permission->loaded)
		{
			return FALSE;
		}
		
		$link['pid'] = $pid;
		
		
		$module_id = $permission->module_id;
		
		// any standard link must have a module_id if it's a new format one
		if (!empty($module_id))
		{
			
			switch ($permission->type) 
			{
				
				case 'a':
					$link['action']		= $permission->permission;
					$link['controller']	= str_replace('controller', '', $controller->load_identifier_value($permission->component_id));
					$link['module']		= $module->load_identifier_value($permission->module_id);
					break;
					
				case 'c':
					$link['controller']	= str_replace('controller', '', $controller->load_identifier_value($permission->component_id));
					$link['module']		= $module->load_identifier_value($permission->module_id);
					break;
				
				case 'm':
					$link['module']		= $module->load_identifier_value($permission->module_id);
					break;
				
			}
			
		}
		
		// we may also have additional parameters to appent to the link
		if ($permission->has_parameters == 't')
		{
			
			$parameters = new PermissionParametersCollection();
			$sh = new SearchHandler($parameters, FALSE);

			$sh->addConstraint(new Constraint('permissionsid', '=', $permission->id));
			
			$parameters->load($sh);
			
			$data = $parameters->getArray();
			
			if (!empty($data))
			{
				
				// loop through any parameters, append to the link array
				foreach ($data as $item)
				{
					$link[$item['name']] = $item['value'];	
				}
				
			}
		
		
		}
		
		return $link;
		
	}
	
	function getPermissions($permissions, $types, $parent_ids = '')
	{
		
		$cc = new ConstraintChain();
		
		if (is_array($permissions))
		{
			$permissions = implode("','", $permissions);
		}
		
		$cc->add(new Constraint('permission', 'in', "('" . $permissions . "')"));
		
		if (is_array($types))
		{
			$types = implode("','", $types);
		}
		
		$cc->add(new Constraint('type', 'in', "('" . $types . "')"));

		if (!empty($parent_ids))
		{
			
			if (is_array($parent_ids))
			{
				$parent_ids = implode(',', $parent_ids);
			}
			
			$cc->add(new Constraint('parent_id', 'in', '(' . $parent_ids . ')'));
		
		}
		
		return $this->getAll($cc, TRUE);
		
	}
	
}

// end of Permission.php
