<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SimpleRoute extends BaseRoute {
	public function __construct ($template, $predefined_arguments=array()) {
		// Convert regex to named captures
		$regex = preg_replace(
			'#{([^}]+)}#',
			'(?P<$1>[^/]+)',
			$template
		);
		
		$this->regex = '^' . $regex . '$';
		$this->predefined_arguments = $predefined_arguments;
	}
}

?>