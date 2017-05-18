<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMappingCollection extends DataObjectCollection {

	protected $version='$Revision: 1.4 $';
	
	function __construct($do='DataMapping', $tablename='data_mappings_overview') {
		
// Contruct the object
		parent::__construct($do, $tablename);
		
	}

}
?>