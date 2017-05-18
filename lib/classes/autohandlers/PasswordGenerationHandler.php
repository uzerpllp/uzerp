<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PasswordGenerationHandler {
	function handle(DataObject $model) {
		$characters = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9');
		$return = '';
		for ($i=0;$i<=mt_rand(8,12);$i++)
			$return .= $characters[mt_rand(0,count($characters)-1)];
		return $return;
	}
}
?>