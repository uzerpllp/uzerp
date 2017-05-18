<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HRParameters extends DataObject
{

	protected $version = '$Revision: 1.1 $';
	
	public function __construct($tablename = 'hr_parameters')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		
		// Define relationships
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
						
		// Define default values
		
		// Define field formatting
	
		// Define link rules for related items
	
	}
	
	public function get_week_dates(&$errors = array())
	{
		
		if (!$this->isLoaded())
		{
			$this->loadBy('usercompanyid', EGS_COMPANY_ID);
		}
		
		if (!$this->isLoaded())
		{
			$errors[] = 'Cannot find HR Parameters';
			return FALSE;
		}
		
		$dates['week_start_date'] = fix_date(date(DATE_FORMAT, strtotime('last '.$this->week_start_day))).' '.$this->week_start_time.':00';
		$dates['week_end_date'] = fix_date(date(DATE_FORMAT, strtotime($dates['week_start_date'].' + 7day'))).' '.$this->week_start_time.':00';
		
		return $dates;
		
	}

	public function load()
	{
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));
		
		parent::load($cc);
		
		if (!$this->isLoaded())
		{
			return FALSE;
		}
		
	}
	
}

// End of HRParameters
