<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class URLFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.3 $';
	
	public $is_safe = true;

	function format($value)
	{
		
		if (empty($value))
		{
			return '';
		}
		
		return '<a class="website" href="http://' . str_replace('http://', '', $value) . '">' . uzh($value) . '</a>';
		
	}
	
}

// end of URLFormatter.php
