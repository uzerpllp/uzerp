<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class RenameColumnComponent extends MigrationComponent {
	protected $from;
	protected $to;
	protected $table;
	
	function __construct() {
		$this->mig_type='rename_column';
	}
	

	function Factory($data) {
		if(isset($data['table'])) {
			$new_data=array();
			$new_data['name']=$data['table'];
			$new_data['rename_column']=array('from'=>$data['from'],'to'=>$data['to']);
			$alter_table = AlterTableComponent::Factory($new_data);
			return $alter_table;			
		}
		$rename_column = new RenameColumnComponent();
		$rename_column->from=$data['from'];
		$rename_column->to=$data['to'];
		return $rename_column;
	}
	
	function toArray() {
		$array=array();
		$array['from']=$this->from;
		$array['to']=$this->to;
		return $array;
	}
	
	function toSQL() {
		$sql='RENAME COLUMN '.$this->from.' TO '.$this->to;
		return $sql;
	}
	
	function __set($key,$val) {
		if($key=='from'||$key=='to')
			$this->$key=$val;
	}

}
?>