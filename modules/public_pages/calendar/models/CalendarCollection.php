<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CalendarCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct($do='Calendar', $tablename='calendars_overview') {
		parent::__construct($do, $tablename);
		
	}
}
?>
