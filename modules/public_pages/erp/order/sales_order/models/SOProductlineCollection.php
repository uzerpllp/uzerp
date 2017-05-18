<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOProductlineCollection extends DataObjectCollection
{

	protected $version = '$Revision: 1.13 $';
	
	public $field;
	
	function __construct($do = 'SOProductline', $tablename = 'so_productlines_overview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby = array('description', 'customer');
		
	}
	
	public function getExportList ()
	{
		return array(fix_date(date(DATE_FORMAT))=>fix_date(date(DATE_FORMAT)));
	}
	
}

// End of SOProductlineCollection
