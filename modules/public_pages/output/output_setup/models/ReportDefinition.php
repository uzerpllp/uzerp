<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportDefinition extends DataObject
{

	protected $version='$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'report_type'
										   ,'user_defined');
	
	protected $do;
	
	function __construct($tablename = 'report_definitions')
	{
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'name';
		$this->orderby			= 'name';
		 		
		// Define relationships
		$this->belongsTo('ReportType', 'report_type_id', 'report_type');
		
		// Define field formats
		
		// Define validators
		
		// Define enumerated types
		
	}
	
	public static function getDefinition($name)
	{
		$report_definition = DataObjectFactory::Factory('ReportDefinition');
		
		$report_definition->loadBy('name', $name);
		
		return $report_definition;
	}
	
	public static function getDefinitionByID($id)
	{
		$report_definition = DataObjectFactory::Factory('ReportDefinition');
	
		$report_definition->loadBy('id', $id);
	
		return $report_definition;
	}
	
	public static function getReportsByType($_report_type_id)
	{
		$report_definition = DataObjectFactory::Factory('ReportDefinition');
		
		$report_definition->idField = 'name';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('report_type_id', '=', $_report_type_id));
		
		return $report_definition->getAll($cc);
		
	}
}

// End of ReportDefinition
