<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class DateFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.3 $';
	
	function format($value)
	{
		
		if (!empty($value))
		{
			return date(DATE_FORMAT,strtotime($value));
		}
		else
		{
			return '';
		}
		
	}
	
}

// end of DateFormatter.php