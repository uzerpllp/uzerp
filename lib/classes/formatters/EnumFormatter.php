<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EnumFormatter implements FieldFormatter {

	protected $version = '$Revision: 1.5 $';
	
	private $options = array();
	
	function __construct($options = array())
	{
		$this->options = $options;
	}
	
	function format($value)
	{
		
		if (!isset($this->options[$value]))
		{
			return '';
		}
		
		return $this->options[$value];
		
	}
	
}

// end of EnumFormatter.php