<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STTransactionCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='STTransaction', $tablename='st_transactionsoverview') {
		parent::__construct($do, $tablename);
			
	}

}
?>