<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Uzlet extends DataObject
{
	
	protected $version = '$Revision: 1.4 $';
	
	protected $defaultDisplayFields = array('name',
											'title',
											'preset',
											'enabled',
											'dashboard');

	protected $do;

	function __construct($tablename = 'uzlets')
	{
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		$this->identifierField	= 'name';
		$this->orderby			= 'name';
		
		// Define relationships
		$this->hasMany('UzletCall','calls','uzlet_id');
		$this->hasMany('UzletModule','modules','uzlet_id');
		
	}
	
	function getCalls()
	{
		$uzlet_calls='';
		
		foreach($this->calls->getContents() as $key=>$value)
		{
			$uzlet_calls.=$value->func.":".$value->arg."\n";
		}
		
		return $uzlet_calls;
	}
	
	function getModules()
	{
		$selected_modules=array();
		
		foreach($this->modules->getContents() as $key=>$value)
		{
			$selected_modules[] = $value->module_id;
		}
		
		return $selected_modules;
	}
}

// End of Uzlet
