<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class IntrastatTransType extends DataObject {

	protected $version='$Revision: 1.1 $';
	
	function __construct($tablename='intrastat_trans_types') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField = "code ||' - '|| description";
		 
	}

}
?>