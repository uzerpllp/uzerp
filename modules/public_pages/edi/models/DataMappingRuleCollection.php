<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataMappingRuleCollection extends DataObjectCollection {

	protected $version='$Revision: 1.3 $';
	
	function __construct($do='DataMappingRule', $tablename='data_mapping_rules_overview') {
// Contruct the object
		parent::__construct($do, $tablename);
		
	}

}
?>