<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Newsletterurlclick extends DataObject {
	
	protected $defaultDisplayFields = array('person'
										   ,'url'
										   ,'clicked_at'
										   );
	
	function __construct($tablename='newsletter_url_clicks') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		//$this->belongsTo('Newsletterurl', 'url_id', 'url');
 		$this->belongsTo('Person', 'person_id', 'person'); 
		$this->orderby='clicked_at';
		$this->orderdir='DESC';
		$this->setAdditional('url');
		$this->setAdditional('newsletter');

	}

}
?>