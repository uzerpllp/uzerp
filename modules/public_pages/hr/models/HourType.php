<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HourType extends DataObject
{

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'group_id');
	
	public function __construct($tablename = 'hour_types')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		
		// Define relationships
		$this->belongsTo('HourTypeGroup','group_id','group');
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}

}

// End of HourType
