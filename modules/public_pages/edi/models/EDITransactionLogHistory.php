<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EDITransactionLogHistory extends EDITransactionLog {

	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='edi_transactions_log_history') {
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		
// Define relationships
		$this->belongsTo('EDITransactionLog', 'edi_transactions_log_id', 'edi_transactions_log');
		
// Define enumerated types
		
// Define system defaults
		
// Define field formats		
	
	}

}
?>