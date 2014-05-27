<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DeliveryTerm extends DataObject {

	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='sy_delivery_terms') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField = "code ||' - '|| description";
		 
	}

}
?>