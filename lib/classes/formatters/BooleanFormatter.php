<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class BooleanFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.4 $';
	
	public $is_safe = true;
	public $is_html = true;

	function format($value)
	{
		
		if ($this->is_html)
		{
			$value = '<img src="/assets/graphics/'.(($value=='t')?'true':'false').'.png" alt="'.$value.'" />';
		}
		else
		{
			$value = (($value=='t')?'Yes':'No');
		}
		
		return $value;
		
	}
	
}

// end of CooleanFormatter.php