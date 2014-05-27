<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HRAuthoriser extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
//	protected $defaultDisplayFields = array('name'
//										   ,'group_id');
	
	protected $authorisation_types = array('Expenses'	=> 'E'
									 	  ,'Holidays'	=> 'H');
	
	public function __construct($tablename = 'hr_authorisers')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->identifierField	= array('employee', 'authorisation_type');
		
		// Define relationships
		$this->belongsTo('Employee','employee_id','employee');
		
		// Define field formats
		
		// set formatters, more set in load() function
		
		// Define enumerated types
		$this->setEnum('authorisation_type', array($this->authorisation_types['Expenses']=>'Expenses'
												  ,$this->authorisation_types['Holidays']=>'Holidays'));
		
		// Define default values
		
		// Define field formatting
		
		// Define link rules for related items
		
	}
	
	function canAuthorise($cc = '', $_authorisation_type = '')
	{
		$this->idField	= 'employee_id';
		$this->orderby	= $this->identifierField	= 'employee';
		
		// Remove any constraints other than those in $cc
		$this->_policyConstraint = null;
		
		if (!($cc instanceof ConstraintChain))
		{
			$cc = new ConstraintChain();
		}
		
		if (!empty($_authorisation_type))
		{
			if (is_array($_authorisation_type))
			{
				$cc->add(new Constraint('authorisation_type', 'in', '(' . implode(',', $_authorisation_type . ')')));
			}
			else
			{
				$cc->add(new Constraint('authorisation_type', '=', $_authorisation_type));
			}
		}
		
		return $this->getAll($cc, null, TRUE);
		
	}
	
	function getTypesForEmployee($_employee_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));

		$this->identifierField	= 'authorisation_type';
		
		return $this->getAll($cc);
		
	}
	
	function expenses_type()
	{
		return $this->authorisation_types['Expenses'];
	}
	
	function holidays_type()
	{
		return $this->authorisation_types['Holidays'];
	}
	
}

// End of HRAuthoriser
