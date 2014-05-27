<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectworktypeCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Projectworktype', $tablename='projectworktypesoverview') {
		parent::__construct($do, $tablename);
		$this->identifierField='title';

	}
		
}
?>