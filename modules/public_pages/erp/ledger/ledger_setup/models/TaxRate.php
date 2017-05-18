<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TaxRate extends DataObject {

	protected $version='$Revision: 1.4 $';
	
	function __construct($tablename='taxrates') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		$this->validateUniquenessOf('taxrate'); 
		$this->identifierField = 'description';
	}

}
?>