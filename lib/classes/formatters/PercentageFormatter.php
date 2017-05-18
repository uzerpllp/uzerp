<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PercentageFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.4 $';
	
	function format($value)
	{
		return h($value) . '%';
	}
	
}

// end of PercentageFormatter.php