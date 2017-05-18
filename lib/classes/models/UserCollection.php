<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UserCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='User', $tablename='useroverview') {
		parent::__construct($do, $tablename);
			
		$this->orderby='username';
	}
		
}
?>
