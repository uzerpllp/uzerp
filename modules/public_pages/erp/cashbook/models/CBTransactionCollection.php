<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CBTransactionCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	
	public $field;
		
	function __construct($do='CBTransaction', $tablename='cb_transactionsoverview') {
		parent::__construct($do, $tablename);

		$this->orderby='transaction_date';
		$this->direction='DESC';

	}
		
}
?>