<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CalendarShareCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='CalendarShare', $tablename='calendar_shares_overview') {
		parent::__construct($do, $tablename);
			
		}
		
}
?>
