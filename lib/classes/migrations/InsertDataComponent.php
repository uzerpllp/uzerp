<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class InsertDataComponent extends MigrationComponent {
	protected $data;
	protected $name;
	function __construct($tablename) {
		$this->data=new Hash();
		$this->mig_type='insert_data';
		$this->name=$tablename;
	}
	
	function addRow($row) {
		$this->data->add($row);
	}
	
	function Factory($data) {
		$insert_data=new InsertDataComponent($data['name']);
		foreach($data['data'] as $row) {
			$insert_data->addRow($row);
		}
		return $insert_data;
	}
	
	function toSQL() {
		$db=DB::Instance();
		$sql='';
		foreach($this->data as $row) {
			$sql.='INSERT INTO '.$this->name.' ';
			$columns='(';
			$values='(';
			foreach($row as $key=>$val) {
				$columns.=$key.',';
				$values.=((!empty($val))?$db->qstr($val):'NULL').',';
			}
			$columns=substr($columns,0,-1);
			$columns.=')';
			$values=substr($values,0,-1);
			$values.=')';
			$sql.=$columns.' VALUES '.$values.";\n";
		}
		return $sql;
		
	}
	
	function toArray() {
		$array=array();
		$array['name']=$this->name;
		$array['data']=$this->data->toArray();		
		return $array;
	}
}
?>