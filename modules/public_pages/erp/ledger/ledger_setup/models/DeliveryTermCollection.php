<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DeliveryTermCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.2 $';
	public $field;
	public $title='Delivery Terms';
	
	function __construct($do='DeliveryTerm') {
		parent::__construct($do);
			
	}

}
?>