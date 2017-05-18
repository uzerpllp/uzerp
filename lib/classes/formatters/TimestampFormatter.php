<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TimestampFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.3 $';
	
	function format($value)
	{
		
		if (empty($value))
		{
			return "";
		}
		
		return date(DATE_TIME_FORMAT, strtotime($value));
		
	}
	
}

// end of TimestampFormatter.php