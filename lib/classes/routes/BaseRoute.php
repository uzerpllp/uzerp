<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
abstract class BaseRoute {
	protected $regex;
	protected $predefined_arguments;
	
	abstract public function __construct();

	public function GetRegex () {
		return $this->regex;
	}
	
	public function GetPredefinedArguments() {
		return $this->predefined_arguments;
	}
}
?>