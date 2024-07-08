<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
/**
 * Handles the displaying of the page called with no arguments
 */
class DefaultController extends Controller {
	
	protected $version='$Revision: 1.2 $';
	
	public function index($collection = null, $sh = '', &$c_query = null) {
		global $smarty;
		
		echo "default";		
	}
	
	
}




?>
