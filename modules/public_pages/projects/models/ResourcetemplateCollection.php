<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ResourcetemplateCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Resourcetemplate', $tablename='resource_templates_overview') {
		parent::__construct($do, $tablename);

		$this->identifierField='id';
	}
		
}
?>