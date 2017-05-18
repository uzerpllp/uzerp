<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFShiftOutputCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.3 $';
	
	public $field;
		
	function __construct($do = 'MFShiftOutput', $tablename = 'mf_shift_outputs_overview')
	{
		
		parent::__construct($do, $tablename);
			
	}
	
}

// End of MFShiftOutputCollection
