<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketAttachment extends DataObject {
	
	protected $defaultDisplayFields = array(
		'file'=>'Name',
		'type'=>'Type',
		'size'=>'Size',
	);
	
	function __construct($tablename='ticket_attachments') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->belongsTo('Ticket', 'ticket_id');
		$this->belongsTo('File', 'file_id');
		$this->getField('size')->setDefault('dummy');
		$this->getField('type')->setDefault('dummy');
	}

}
?>