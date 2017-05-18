<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Country extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename='countries') {
		parent::__construct($tablename);
		$this->idField='code';

	}

}
?>