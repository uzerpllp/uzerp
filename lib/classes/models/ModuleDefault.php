<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleDefault extends DataObject
{

	protected $version='$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('field_name'
										   ,'system_default_value'
										   ,'default_value');
	
	function __construct($tablename='module_defaults')
	{
		parent::__construct($tablename);

		$this->idField='id';
		
		$this->orderby='field_name';
		$this->identifierField='field_name';
		
		$this->hasOne('ModuleComponent', 'module_components_id', 'module_component');
	
	}
	
	static function getDefaultValue($module_components_id, $field_name)
	{
		$default=DataObjectFactory::Factory('ModuleDefault');
		$default->loadBy(array('module_components_id', 'field_name'), array($module_components_id, $field_name));
		return $default->default_value;
	}
	
	static function getValue($model, $field)
	{
		if (isset($model->belongsToField[$field->name]))
		{
			$x = DataObjectFactory::Factory($model->belongsTo[$model->belongsToField[$field->name]]["model"]);
			$x->load($field->default_value);
			return $x->getIdentifierValue();
		}
		elseif ($model->isEnum($field->name))
		{
			return $model->getEnum($field->name, $field->default_value);
		}
		else
		{
			return $field->default_value;
		}
	}
	
}

// End of ModuleDefault
