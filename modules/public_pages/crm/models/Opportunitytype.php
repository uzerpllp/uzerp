<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Opportunitytype extends DataObject {

	function __construct($tablename='opportunitytype') {
		parent::__construct($tablename);
		$this->idField='id';
	}

}
?>