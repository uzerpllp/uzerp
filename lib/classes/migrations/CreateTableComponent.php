<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CreateTableComponent extends MigrationComponent{
	
	protected $name;
	protected $columns;
	protected $constraints;
	
	function __construct() {
		$this->mig_type='create_table';
		$this->columns=new Hash();
		$this->constraints=new Hash();
	}
	public static function Factory($data) {
	
		$component=new CreateTableComponent();
		$component->name=$data['name'];
		foreach($data['columns'] as $column_data) {
			$component->addColumn(new AddColumnComponent($column_data));
		}
		if(isset($data['constraints'])&&is_array($data['constraints'])) {
			foreach($data['constraints'] as $constraint_data) {
				$component->constraints->add($constraint_data);
			}
		}
		return $component;
	
	}
	function toArray() {
		$array=array();
		$array['name']=$this->name;
		foreach($this->columns as $cc) {
			$array['columns'][]=$cc->toArray();
		}
		$array['constraints']=$this->constraints->toArray();
		return $array;
	}
	
	function __set($var,$val) {
		if($var=='name')
			$this->name=$val;
		if($var instanceof AddColumnComponent)
			$this->addColumn($var);
	}
	
	function toSQL() {
		$sql='CREATE TABLE '.$this->name." (\n";
		foreach($this->columns as $cc) { //column_component
			$sql.=$cc->toSQL().",\n";
		}
		
		foreach($this->constraints as $key=>$val) {
			if($key=='primary_key') {
				$sql.='PRIMARY KEY('.implode(',',$val)."),\n";
			}
		}
		$sql=substr($sql,0,-2)."\n";
		$sql.=");\n";

		return $sql;
	}
	
	function setPrimaryKeys($columns) {
		if(count($columns)==1) {
			foreach($this->columns as $cc) {
				if($cc->name==$columns[0])
					$cc->setPrimaryKey();
			}
		}
		else {
			$this->constraints->set('primary_key',$columns);
		}
	}
	
	function addColumn(AddColumnComponent $column) {
		$this->columns->add($column);
	}

}

?>