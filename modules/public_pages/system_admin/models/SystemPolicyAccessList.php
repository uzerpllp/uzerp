<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemPolicyAccessList extends DataObject
{

	protected $version='$Revision: 1.3 $';

	protected $defaultDisplayFields = array('access_type'
										   ,'name');

	function __construct($tablename='sys_policy_access_lists')
	{
// Register non-persistent attributes
//		$this->setAdditional('name');

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';

		$this->identifierField	= array('access_type', 'name');
		$this->orderby			= array('access_type', 'name');

// Define validation

// Define relationships

// Define enumerated types
		$this->setEnum('access_type', array('Permission'=>'Permission'
										   ,'Role'=>'Role'));

// Define system defaults

// Define field formats		

// Define View Related Link Rules

	}

	function getAccessValues($_access_type = '')
	{
		if (empty($_access_type))
		{
			return array();
		}

		$cc = new ConstraintChain();

		if ($_access_type == 'Permission')
		{
			$cc->add(new Constraint('type', 'in', "('g', 'm')"));
			$use_collection = TRUE;
			$order_by = 'permission';
		}
		else
		{
			$use_collection = FALSE;

		}

		$access_type = DataObjectFactory::Factory($_access_type);

		if (!empty($order_by)) { $access_type->orderby = $order_by; }

		return $access_type->getAll($cc, FALSE, $use_collection);

	}

	function getAccessValue()
	{

		if ($this->isLoaded())
		{

			$access_type = DataObjectFactory::Factory($this->access_type);

			$access_type->load($this->access_object_id);

			return $access_type->getIdentifierValue();

		}

	}

	function getAll(ConstraintChain $cc = null, $ignore_tree = FALSE, $use_collection = FALSE, $limit = '')
	{
		return parent::getAll($cc, $ignore_tree, TRUE, $limit);
	}

	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}

}

// End of SystemPolicyAccessList
