<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataDefinitionCollection extends DataObjectCollection {

	protected $version='$Revision: 1.3 $';
	
	function __construct($do='DataDefinition', $tablename='data_definitions_overview') {
// Contruct the object
		parent::__construct($do, $tablename);
		
	}

}
?>