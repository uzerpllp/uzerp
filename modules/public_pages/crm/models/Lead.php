<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Lead extends Company {
	protected $defaultDisplayFields=array('name'
										 ,'town'
										 ,'phone'
										 ,'website');

	function __construct() {
		parent::__construct();
		$this->getField('accountnumber')->dropnotnull();
		unset($this->_autohandlers['accountnumber']);
	}

}
?>