<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ActivityNoteCollection extends DataObjectCollection {
	
		public $field;
		
	function __construct($do='ActivityNote', $tablename='activity_notes') {
		parent::__construct($do, $tablename);

	}
		
}
?>
