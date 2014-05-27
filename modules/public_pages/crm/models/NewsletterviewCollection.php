<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NewsletterviewCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Newsletterview', $tablename='newsletter_viewsoverview') {
		parent::__construct($do, $tablename);
			
		$this->view='';
	}
		
}
?>