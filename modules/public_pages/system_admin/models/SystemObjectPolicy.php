<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystemObjectPolicy extends DataObject
{

	protected $version='$Revision: 1.5 $';

	protected $defaultDisplayFields = array('name'
										   ,'module_component'
										   ,'fieldname'
										   ,'operator'
										   ,'value'
										   ,'is_id_field'
										   ,'module_components_id');

	private $_component_model = null;

	function __construct($tablename='sys_object_policies')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';

		$this->identifierField='name';
		$this->orderby = 'name';

// Define validation

// Define relationships
		$this->belongsTo('ModuleComponent', 'module_components_id', 'module_component');
//		$this->hasMany('ModuleComponent', 'module_components_id', 'module_component');

// Define enumerated types
		$this->setEnum('operator', array('='=>'Equals'
										,'>'=>'Greater Than'
										,'<'=>'Less Than'
										,'>='=>'Equal to or Greater Than'
										,'<='=>'Less Than or equal to'
										,'!='=>'Not equal to'
										,'is'=>'is'));

		$this->setEnum('multiple', array('='=>'Equals'
										,'!='=>'Not equal to'));

// Define system defaults

// Define field formats		

// Define View Related Link Rules

	}

	function getComponentTitle()
	{

		return $this->get_model()->getTitle();

	}

	function get_field()
	{

		if ($this->idField == $this->fieldname)
		{
			return 'key_field';
		}

		$model = $this->get_model();

		if (isset($model->belongsToField[$this->fieldname]))
		{
			return $model->belongsToField[$this->fieldname];
		}

		return $this->fieldname;

	}

	function get_model ()
	{
		if (is_null($this->_component_model))
		{
			$this->_component_model = DataObjectFactory::Factory($this->module_component);
		}

		return $this->_component_model;

	}

	function getvalue()
	{

		$model = $this->get_model();

		if ($this->idField == $this->fieldname)
		{

			$cc = new ConstraintChain();

			if (substr($this->value,-1)==')')
			{
				$cc->add(new Constraint($model->idField, 'IN', $this->value));

			}
			else
			{
				$cc->add(new Constraint($model->idField, '=', $this->value));
			}

			$values = $model->getAll($cc);

			if (count($values) > 0)
			{
				return implode(',', $values);
			}
		}
		elseif (isset($model->belongsToField[$this->fieldname]))
		{
			if ($this->value === "'NULL'")
			{
				return 'NULL';
			}

			$fk = DataObjectFactory::Factory($model->belongsTo[$model->belongsToField[$this->fieldname]]['model']);

			$fk->load($this->value);

			return $fk->getIdentifierValue();
		}
		elseif ($model->isEnum($this->fieldname))
		{
			return $model->getEnum($this->fieldname, $this->value);
		}

		return $this->value;

	}

	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}

}

// End of SystemObjectPolicy
