<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SODespatchEventCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	
	public $field;

	function __construct($do='SODespatchEvent',$tablename='so_despatchevents') {
		parent::__construct($do, $tablename);
			
	}	
		
}
?>