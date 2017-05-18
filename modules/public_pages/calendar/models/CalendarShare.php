<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CalendarShare extends DataObject {

	protected $defaultDisplayFields = array('id','calendar_id','username');

	function __construct($tablename='calendar_shares') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->belongsTo('Calendar','calendar_id','calendar');
		
	}
}
?>
