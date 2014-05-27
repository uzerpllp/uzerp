<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleObject extends DataObject
{

	protected $version='$Revision: 1.12 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'description'
										   ,'registered'
										   ,'enabled'
										   ,'location'
										   ,'created'
										   ,'help_link'
										   );
	
	protected $_title = 'Module';
	
	function __construct($tablename='modules')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField='id';
 		
		$this->validateUniquenessOf('name');
		$this->validateUniquenessOf('location');
		
// Set ordering attributes
		$this->orderby='name';
		$this->identifierField='name';
		
// Define relationships
		$this->hasMany('ModuleComponent', 'module_components', 'module_id');
		$this->getField('name')->setFormatter(new NullFormatter());

// Define enumerated types
		
// Define field formats
		$this->getField('help_link')->setFormatter(new URLFormatter());
		$this->getField('help_link')->type = 'html';
		
// Define system defaults

	}

	function getComponentLocations()
	{

		$components=new ModuleComponent();
		$components->idField='name';
		$components->identifierField='location';
		$cc=new ConstraintChain();
		$cc->add(new Constraint('module_id', '=', $this->id));
		$cc->add(new Constraint('type', 'in', "('C', 'E', 'M', 'R')"));
		
		return $components->getAll($cc);
		
	}
	
	function isRegistered()
	{
		return ($this->module_components->count()>0);
	}

	function disable ()
	{

		$permission = New Permission();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('permission', '=', $this->name));
		$permission->loadBy($cc);
		if ($permission && $permission->delete())
		{
			return $this->update($this->id, 'enabled', false);
		}
		return true;
	}
	
	function enable (&$errors=array(), $menu_options=array())
	{

		$position=Permission::getNextPosition();
		if (!$position)
		{
			$position=0;
		}
		$data = array('permission'=>$this->name
					 ,'type'=>'m'
					 ,'description'=>$this->description
					 ,'title'=>prettify($this->name)
					 ,'display'=>true
					 ,'position'=>$position+1);
		
		$m_parent_id=$this->addPermission($data, $errors);
		if (!$m_parent_id)
		{
			return false;
		}

		$c_position=1;
		
		foreach ($this->module_components as $component)
		{
			switch ($component->type) {
				case 'C':
					$name=str_replace('controller', '', strtolower($component->name));
					if (isset($menu_options[$name]))
					{
						$new_menu=$menu_options[$name];
						$data = array('permission'=>$new_menu['permission']
								 ,'type'=>'c'
								 ,'description'=>''
								 ,'title'=>$new_menu['title']
								 ,'display'=>$new_menu['display']
								 ,'position'=>$c_position++
								 ,'parent_id'=>$m_parent_id);
						$c_parent_id=$this->addPermission($data, $errors);
						
						if (!$c_parent_id)
						{
							return false;
						}
						else
						{
							foreach ($new_menu['actions'] as $a_position=>$action)
							{
								$data=$action;
								$data['type']='a';
								$data['position']=$a_position+1;
								$data['parent_id']=$c_parent_id;
								if (!$this->addPermission($data, $errors))
								{
									return false;
								}
							}
						}
					}
					break;
			}
		}
		return $this->update($this->id, 'enabled', true);
		
	}
	
	function unregister ()
	{
		
		$sh=new SearchHandler($this->module_components, false);
		$sh->addConstraint(new Constraint('module_id', '=', $this->id));
		$this->module_components->delete($sh);
		
		return $this->update($this->id, 'registered', false);
		
	}

	private function addPermission ($data, &$errors=array())
	{

		$permission=Permission::exists($data);
		if (!$permission || !$permission->isLoaded())
		{
			$permission=DataObject::Factory($data, $errors, 'Permission');
			if (!$permission || !$permission->save())
			{
				return false;
			}
			else
			{
				return $permission->id;
			}
		}
		else
		{
			return $permission->id;
		}
		
	}
	
	static function getModule($_module_name)
	{
		
		$module = DataObjectFactory::Factory(__CLASS__);
		
		$module->loadBy('name', $_module_name);
		
		return $module;
	}
	
	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}
	
}

// End of ModuleObject
