<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrencyRate extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='curate') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		$this->validateUniquenessOf(array('date', 'rate'));
 		$this->belongsTo('Currency', 'currency_id', 'currency'); 

	}

}
?>