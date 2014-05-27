<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleComponent extends DataObject
{

	protected $version = '$Revision: 1.16 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'type'
										   ,'location');
	
	function __construct($tablename = 'module_components')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		
		$this->orderby			= 'name';
		$this->identifierField	= 'name';
		
// 		$this->validateUniquenessOf(array('permission', 'parent_id', 'type'));

// Define relationships
		$this->belongsTo('ModuleObject', 'module_id', 'module_name');
		$this->hasOne('ModuleObject', 'module_id', 'module');
		$this->hasMany('ModuleDefault', 'module_defaults', 'module_components_id');
		$this->hasMany('SystemObjectPolicy', 'system_policies', 'module_components_id');
		
// Define enumerated types
		$this->setEnum('type'
					  ,array('C'=>'Controller'
							,'E'=>'Eglet'
							,'J'=>'Javascript'
							,'M'=>'Model'
							,'R'=>'Report'
							,'S'=>'Stylesheet'
							,'T'=>'Template'));
	
// Define system defaults
		$this->getField('type')->setDefault('M');
							
// Define field formats		
		$this->getField('help_link')->setFormatter(new URLFormatter());
		$this->getField('help_link')->type = 'html';
		
// Define View Related Link Rules		

	}

	static function &Instance($do, $type)
	{
		static $modulecomponents = array();
		
		$name=strtolower(get_class($do));
		
		if (!isset($modulecomponents[$name][$type]))
		{
			
			if ($do instanceof ModuleComponent)
			{
				$modulecomponent=$do;
			}
			else
			{
				$modulecomponent=DataObjectFactory::Factory('ModuleComponent');
			}
			
			$result=false;
			$result=$modulecomponent->loadBy(array('name', 'type'), array($name, $type));
			
			if($result!==false && defined('EGS_COMPANY_ID') && EGS_COMPANY_ID>0)
			{
				$modulecomponents[$name][$type]=$modulecomponent;
			}
			else
			{
				$modulecomponents[$name][$type]=array();
			}
		}
		return $modulecomponents[$name][$type];
	}

	static function getModelList()
	{
		$module_component = DataObjectFactory::Factory('ModuleComponent');
		
		$module_component->identifierField = $module_component->orderby = 'title';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', 'M'));
		
		// Really need to identify the DataObject models
		$cc->add(new Constraint('name', 'not like', '%collection'));
		
		$cc->add(new Constraint('name', 'not like', '%search'));
		
		return $module_component->getAll($cc);
		
	}
	
	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}
	
	static function getModuleComponent($_module_name, $_type)
	{
		
		$modulecomponent = DataObjectFactory::Factory(__CLASS__);
		
		$modulecomponent->loadBy(array('name', 'type'), array($_module_name, $_type));
		
		return $modulecomponent;
	}
	
	static function getComponentId($_module_name, $_component_name)
	{
		$module = ModuleObject::getModule($_module_name);
		
		$modulecomponent = DataObjectFactory::Factory(__CLASS__);
				
		$modulecomponent->loadBy(array('name', 'module_id'), array($_component_name, $module->id));
		
		return $modulecomponent->id;
	}
	
}

// End of ModuleComponent