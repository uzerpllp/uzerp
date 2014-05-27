<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportType extends DataObject
{

	protected $version = '$Revision: 1.1 $';
	
	function __construct($tablename = 'report_types')
	{
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'name';
		$this->orderby			= 'name';
				
		// Define relationships
		
		// Define field formats
		
		// Define validators
		
		// Define enumerated types
		
	}
	
	public static function getReportTypes()
	{
		$report_type = DataObjectFactory::Factory('ReportType');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('public', 'IS', TRUE));
		
		return $report_type->getAll($cc);
		
	}
}

// End of ReportType
