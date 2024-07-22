<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NullFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.4 $';

	public $is_safe = true;
	public $is_html = false;

	function __construct($is_safe = true)
	{
		$this->is_html = $is_safe;
	}

	public function format($value)
	{
		return $value;
	}

}

// end of NullFormatter.php