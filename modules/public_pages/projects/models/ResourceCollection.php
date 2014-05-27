<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ResourceCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	
	public $field;
		
	function __construct($do='Resource', $tablename='project_resources_overview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='id';
	}
	
}
?>