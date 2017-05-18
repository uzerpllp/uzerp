<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CountryCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	
	public $field;
	
	function __construct($do='Country', $tablename='countriesoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>