<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportPart extends DataObject
{

	protected $version = '$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('name');
	
	protected $do;
	
	function __construct($tablename = 'report_parts')
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
	
	public static function getParts($name)
	{
		$report_definition = DataObjectFactory::Factory('ReportPart');
		
		$report_definition->loadBy('name', $name);
		
		return $report_definition->definition;
	} 
	
}

// End of ReportPart
