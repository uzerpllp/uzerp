<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EDITransactionLogCollection extends DataObjectCollection {

	protected $version='$Revision: 1.3 $';
	
	function __construct($do='EDITransactionLog', $tablename='edi_transactions_log_overview') {

// Contruct the object
		parent::__construct($do, $tablename);
		
	}

}
?>