<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFCentreWasteTypeCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.3 $';
	
	public $field;
		
	function __construct($do = 'MFCentreWasteType', $tablename = 'mf_centre_waste_types_overview')
	{
		
		parent::__construct($do, $tablename);
			
	}
	
}

// End of MFCentreWasteTypeCollection
