<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CSFailureCode extends DataObject
{

	protected $version='$Revision: 1.5 $';
	
	function __construct($tablename='cs_failurecodes')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->orderby='code';
		
		$this->identifierField='code || \'- \' ||description';
 		
// Define relationships

// Define field formats

// Define validation
		$this->validateUniquenessOf('code'); 

// Define enumerated types

	}
	
	public function getInUse()
	{
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('in_use', 'is', true));
		
		return $this->getAll($cc);
		
	}
	
}

// End of CSFailureCode
