<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

abstract class MigrationComponent implements YAMLable, SQLable {
	public $mig_type;
	protected $db;
	abstract function __construct();
	
	function setDB(&$db) {
		$this->db=$db;
	}
}

?>