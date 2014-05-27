<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CampaignstatusCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Campaignstatus') {
		parent::__construct($do);

	}
		
}
?>