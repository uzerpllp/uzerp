<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class RenameTableComponent extends MigrationComponent {
	protected $to;

	function __construct() {
		$this->mig_type='rename_table';
	}
	
	function Factory($data) {
		$rename_table = new RenameTableComponent();
		$rename_table->to=$data['to'];
		return $rename_table;
	}
	
	function toSQL() {
		return 'RENAME TO '.$this->to;
	}
	
	function toArray() {
		$array=array();
		return $array;
	}
	
	function __set($key,$val) {
		if($key=='to')
			$this->$key=$val;
	}
}
?>