<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Debuglines extends DataObject {

	function __construct($tablename='debug_lines') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->hasOne('Debug', 'debug_id', 'header');
	}

}
?>
