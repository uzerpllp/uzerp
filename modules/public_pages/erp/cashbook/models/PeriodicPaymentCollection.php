<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PeriodicPaymentCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	
	public $field;
		
	function __construct($do='PeriodicPayment', $tablename='periodic_paymentsoverview') {
		parent::__construct($do, $tablename);

	}
		
}
?>