<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Language extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename='lang') {
		parent::__construct($tablename);
		$this->idField='code';
		
		$this->identifierField='name';
		
	}

}
?>