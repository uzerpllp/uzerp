<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TrafficLightFormatter implements FieldFormatter {
	
	protected $version = '$Revision: 1.4 $';
	
	public $is_safe = true;
	public $is_html = true;

	function format($value)
	{
		
		if (!($value == 'red' || $value == 'amber' || $value == 'green'))
		{
			return '-';
		}
		
		if ($this->is_html)
		{
			$value = '<img src="/themes/default/graphics/' . $value . '.png" alt="' . $value . '" />';
		}
		
		return $value;
		
	}
	
}

// end of TrafficLightFormatter.php