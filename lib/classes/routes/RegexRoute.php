<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RegexRoute extends BaseRoute {
	public function __construct ($regex, $predefined_arguments=array())
	{
		$this->regex = $regex;
		$this->predefined_arguments = $predefined_arguments;
	}
}

?>