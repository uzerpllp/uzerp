<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CompanyUniqueReferenceHandler extends AutoHandler {
	private $table;
	private $field;
	
	function __construct($table, $field) {
		$this->table = $table;
		$this->field = $field;
	}
	function handle(DataObject $model) {
		$field = $model->{$this->field};
		if(empty($field)) {
			$db=DB::Instance();
			$query='SELECT max(' . $this->field . ') FROM ' . $this->table . ' WHERE usercompanyid='.EGS_COMPANY_ID;
			$current=$db->GetOne($query);
			return $current+1;
		}
	}
}
?>
