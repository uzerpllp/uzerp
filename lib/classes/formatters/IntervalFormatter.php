<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class IntervalFormatter implements FieldFormatter
{

	protected $version = '$Revision: 1.3 $';

	public function format($value)
	{
		return to_working_days($value);
	}

}

// end of IntervalFormatter.php