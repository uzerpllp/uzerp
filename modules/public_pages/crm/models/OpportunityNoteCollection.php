<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunityNoteCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='OpportunityNote', $tablename='opportunity_notes') {
		parent::__construct($do, $tablename);
			
	}
	
}
?>