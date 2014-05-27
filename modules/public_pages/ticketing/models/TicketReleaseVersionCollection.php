<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketReleaseVersionCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	
	function __construct($do='TicketReleaseVersion', $tablename='ticket_release_versions') {
		parent::__construct($do, $tablename);
	}
		
}
?>