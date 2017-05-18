<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketAttachmentCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='TicketAttachment', $tablename='ticket_attachments_overview') {
		parent::__construct($do, $tablename);
		
	}
		
}
?>