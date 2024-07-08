<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends Controller {

	public function __construct($module=null,$action=null) {
		parent::__construct($module,$action);
		$this->sidebar();
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		sendTo('','index',$this->_modules);
	}
	
}
?>
