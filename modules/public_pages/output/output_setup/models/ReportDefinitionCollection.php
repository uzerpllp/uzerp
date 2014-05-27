<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportDefinitionCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.5 $';
	
	public $field;
		
	function __construct($do = 'ReportDefinition')
	{
		parent::__construct($do, 'report_definitions_overview');
		
		$this->title = 'Report Definitions';
	}

}

// End of ReportDefinitionCollection
