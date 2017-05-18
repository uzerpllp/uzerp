<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFShiftWasteCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.3 $';
	
	public $field;
		
	function __construct($do = 'MFShiftWaste', $tablename = 'mf_shift_waste_overview')
	{
		parent::__construct($do, $tablename);
			
	}
	
}

// End of MFShiftWasteCollection
