<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SystemPolicyControlList extends DataObject
{

	protected $version = '$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('policy'
										   ,'access_type'
										   ,'name'
										   ,'type'
										   ,'allowed'
										   ,'object_policies_id'
										   ,'access_lists_id');

	function __construct($tablename = 'sys_policy_control_lists')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';

// Define validation
		
// Define relationships
		$this->belongsTo('SystemObjectPolicy', 'object_policies_id', 'policy');
		$this->belongsTo('SystemPolicyAccessList', 'access_lists_id', 'access_type');
	
// Define enumerated types
		$this->setEnum('type', array('AND'=>'AND'
									,'OR'=>'OR'));
		
		$this->setEnum('allowed', array('t'=>'ALLOW'
									   ,'f'=>'DENY'));
		
// Define system defaults
							
// Define field formats		
	
// Define View Related Link Rules
			
	}
	
	function getContext()
	{
		$permission = DataObjectFactory::Factory('Permission');
		
		$permission->identifierField	= 'title';
		$permission->orderby			= 'title';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('type', 'in', "('g', 'm')"));
		
		return $permission->getAll($cc, false);
		
	}

	function getObjectPolicyValue()
	{
		$policy_detail = DataObjectFactory::Factory('SystemObjectPolicy');
		
		$policy_detail->load($this->object_policies_id);
		
		$policy_value = $policy_detail->getComponentTitle() . ' ' .
						prettify($policy_detail->get_field()) . ' ' .
						$policy_detail->getFormatted('operator') . ' ' .
						$policy_detail->getvalue();
		
		return $policy_value;
		
	}
	
	/*
	 * Override the DataObject method because policies do not apply here
	 */
	function setPolicyConstraint($module_component = '', $field = '')
	{

	}
	
}

// End of SystemPolicyControlList
