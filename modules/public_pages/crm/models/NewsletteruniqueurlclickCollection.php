<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NewsletteruniqueurlclickCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='Newsletterurlclick', $tablename='newsletter_unique_url_clicksoverview') {
		parent::__construct($do, $tablename);

		$this->view='';
	}
		
}
?>