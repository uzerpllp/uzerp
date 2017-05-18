<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Session {

	protected $version='$Revision: 1.2 $';
	
	public static function &Instance() {
		static $Session;
		if(empty($Session))
			$Flash=new Session;
		return $Session;
	}

	public function __get($var) {
		if(!empty($_SESSION[$var]))
			return $_SESSION[$var];
	}
	
	public function __set($key,$var) {
		$_SESSION[$key]=$var;
	}

}
?>