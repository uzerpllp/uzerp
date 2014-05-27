<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class NewsletterCollection extends DataObjectCollection {

	public $field;

	function __construct($do='Newsletter', $tablename='newsletteroverview') {
		parent::__construct($do, $tablename);

	}

}
?>