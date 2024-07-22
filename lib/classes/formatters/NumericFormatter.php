<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NumericFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.2 $';

	private $decimal_places;

	function __construct($decimal_places = 2)
	{
		$this->decimal_places	= $decimal_places;
	}

	function format($value)
	{

		if (!is_numeric($value))
		{
			return $value;
		}

		return number_format($value, $this->decimal_places);

	}

}

// end of NumericFormatter.php