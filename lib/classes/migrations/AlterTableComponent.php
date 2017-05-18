<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class AlterTableActionComponent {
	function Factory($data) {
		switch($data['type']) {
			case 'alter_column':
				return AlterColumnComponent::Factory($data);
				break;		
		}
	}

}
class AlterColumnComponent extends MigrationComponent {
	protected $name;
	protected $options;
	
	function __construct() {
		$this->options=new Hash();
		$this->mig_type='alter_column';
	}
	
	function Factory($data) {
		$alter_column=new AlterColumnComponent();
		$alter_column->name=$data['name'];
		$options=array('drop_not_null','set_not_null');
		foreach($options as $val) {
			if(isset($data[$val]))
				$alter_column->options->set($val,$data[$val]);
		}
		return $alter_column;
	}
	
	function toSQL() {
		$sql='ALTER COLUMN '.$this->name.' ';
		foreach($this->options->toArray() as $key=>$val) {
			if($val===true)
				$sql.=prettify($key);
		}
		
		return $sql;
	}
	
	function toArray() {
		$array=array();
		$array['name']=$this->name;
		foreach($this->options->toArray() as $key=>$val)
			$array[$key]=$val;
		return $array;
	}
	function __get($key) {
		if($key=='options')
			return $this->$key;
	}
	function __set($key,$val) {
		if($key=='name'||$key=='options'||$key=='drop_not_null'||$key=='set_not_null')
			$this->$key=$val;
	}
}
class AlterTableComponent extends MigrationComponent {

	protected $name;
	protected $rename;
	protected $rename_column;
	protected $actions;
		
	function __construct() {
		$this->mig_type='alter_table';
		$this->actions=new Hash();
	}
	public static function Factory($data) {
	
		$component=new AlterTableComponent();
		$component->name=$data['name'];
		if(isset($data['rename'])) { // you can't rename tables/columns in conjunction with anything else
			$component->rename = RenameTableComponent::Factory($data['rename']);
		}
		else if(isset($data['rename_column'])) {
			$component->rename_column = RenameColumnComponent::Factory($data['rename_column']);
		}
		else {	
			foreach($data['actions'] as $action_data) {
				$component->actions->add(AlterTableActionComponent::Factory($action_data));	//will return the appropriate MigrationComponent
			}
		}
		return $component;
	
	}
	function toArray() {
		$array=array();
		$array['name']=$this->name;
		if($this->rename instanceof MigrationComponent) {
			$array['rename']=$this->rename->toArray();
		}
		else if ($this->rename_column instanceof MigrationComponent) {
			$array['rename_column']=$this->rename_column->toArray();
		}
		else {
			foreach($this->actions->toArray() as $mc) {		//they're all MigrationComponents
				$array['actions'][]=$mc->toArray();
			}
		}
		return $array;
	}
	
	function __set($key,$val) {
		if($key=='name'||$key=='rename'||$key=='rename_column'||$key=='actions')
			$this->name=$val;
	}
	function __get($key) {
		if($key=='actions')
			return $this->$key;
	}
	function toSQL() {
		$sql='ALTER TABLE '.$this->name.' ';
		if($this->rename instanceof MigrationComponent) {
			$sql.=$this->rename->toSQL();
		}
		else if ($this->rename_column instanceof MigrationComponent) {
			$sql.=$this->rename_column->toSQL();
		}
		else {
			foreach($this->actions->toArray() as $mc) {		//they're all MigrationComponents
				$sql.=$mc->toSQL().",\n";
			}
			$sql=substr($sql,0,-2);
		}
		
		$sql.=";\n";
		return $sql;
	}
	
}
?>