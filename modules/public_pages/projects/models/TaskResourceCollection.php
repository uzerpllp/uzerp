<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaskResourceCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct($do='TaskResource', $tablename='task_resources_overview') {
			parent::__construct($do, $tablename);
			
		}

}
?>