<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMappingDetailCollection extends DataObjectCollection {

	protected $version='$Revision: 1.3 $';
	
	function __construct($do='DataMappingDetail', $tablename='data_mapping_details_overview') {
		
// Contruct the object
		parent::__construct($do, $tablename);
		
	}

}
?>