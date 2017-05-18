<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PriceFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.4 $';
	
	public $is_safe = true;
	private $is_html = true;

	function __construct($html = true)
	{
		$this->is_html = $html;
	}

	function format($value)
	{
		return pricify($value, $this->is_html);
	}

}

// end of PriceFormatter.php