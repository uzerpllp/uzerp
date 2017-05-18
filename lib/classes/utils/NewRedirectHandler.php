<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NewRedirectHandler implements Redirection{

	protected $version='$Revision: 1.2 $';
	
	public function Redirect() {
		$args=func_get_args();
		if(is_array($args[0]))
			$args=$args[0];
		$location='';
		foreach($args as $key=>$val) {
			$location.=$val.'/';
		}
		if($location!='/')
			$location='/'.$location;
		$flash=Flash::Instance();
		$flash->save();
		header('Location: '.$location);
		exit;

	}
	
}

?>
