<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFDataSheet extends DataObject {

	function __construct($tablename='mf_data_sheets') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField='id || \' - \' ||name';
	}

}
?>