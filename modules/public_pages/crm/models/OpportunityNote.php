<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpportunityNote extends DataObject {

	protected $defaultDisplayFields=array('title'=>'Title','note'=>'Note');

	function __construct($tablename='opportunity_notes') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity');
	}

}
?>