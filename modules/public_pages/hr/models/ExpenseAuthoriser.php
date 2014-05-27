<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenseAuthoriser extends DataObject 
{
	
	protected $version='$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array(
		'employee'			=> 'employee',
		'authoriser'		=> 'authoriser'
	);
	
	public function __construct($tablename = 'expense_authorisers')
	{
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->identifierField = 'authoriser';
		
		// Define relationships
		$this->hasOne('Employee', 'employee_id', 'employee');
		$this->hasOne('Employee', 'authoriser_id', 'authoriser');
	
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
				
	}
	
	public function isAuthorised($_employee_id, $_authoriser_id)
	{
		$this->loadBy(array('employee_id', 'authoriser_id'), array($_employee_id, $_authoriser_id));
		
		return $this->isLoaded();
		
	}
	
	public function isAuthoriserFor($_authoriser_id)
	{
		$this->identifierField = 'employee_id';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('authoriser_id', '=', $_authoriser_id));
		
		return $this->getAll($cc);
		
	}
	
	public function getAuthorisers($_employee_id)
	{
		$this->identifierField	= 'authoriser_id';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));
		
		return $this->getAll($cc);
		
	}
	
	public function getAuthorisersByName($_employee_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $_employee_id));
		
		return $this->getAll($cc, TRUE, TRUE);
		
	}
	
}

// End of ExpenseAuthoriser
