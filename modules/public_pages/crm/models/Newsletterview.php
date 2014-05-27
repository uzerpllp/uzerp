<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Newsletterview extends DataObject {
	
	protected $defaultDisplayFields=array('person'
										 ,'newsletter'
										 ,'time_viewed'
										 ,'ip_address'
										 );
	
	function __construct($tablename='newsletter_views') {
		parent::__construct($tablename);
		$this->idField='id';
		
		$this->orderby='time_viewed';
		$this->orderdir='DESC';
		
 		$this->belongsTo('Person', 'person_id', 'person');
 		$this->belongsTo('Newsletter', 'newsletter_id', 'newsletter'); 

	}

}
?>