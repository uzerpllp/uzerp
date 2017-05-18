<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunitystatusCollection extends DataObjectCollection {
	
	public $field;

	function __construct($do='Opportunitystatus') {
		parent::__construct($do);
	}

}
?>