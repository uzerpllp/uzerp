<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class AddColumnComponent extends MigrationComponent{
	protected $name;
	protected $type;
	protected $options;
	protected $references;
	
	
	function __construct($data) {
		$this->mig_type='add_column';
		$this->options=new Hash();
		$this->references=new Hash();
		$option_keys=array('not_null');
		if(is_array($data)) {
			$this->name=$data['name'];
			$this->type=$data['type'];
			if(isset($data['options'])&&is_array($data['options'])) {
				foreach($data['options'] as $key=>$val) {
					if(!empty($val))
						$this->options->set($key,$val);
				}
			}
			if(isset($data['references'])&&is_array($data['references'])) {
				foreach($data['references'] as $key=>$val) {
					$this->references->set($key,$val);
				}
			}

		}
		else {
			$this->name=$data->name;
			$this->type=$data->type;
		
			foreach($option_keys as $key) {
				if(!empty($data->$key))
					$this->options->set($key,$data->$key);
			}
			
			if(is_array($data->references)) {
				foreach($data->references as $key=>$val) {
					$this->references->set($key,$val);
				}
			}
		}
	}
	function setPrimaryKey() {
		$this->options->set('primary_key',true);
	}
	
	function toSQL() {
		//username varchar not null primary key
		$sql=$this->name.' '.$this->type;
		foreach($this->options as $key=>$val) {
			$sql.=' '.prettify($key);
		}
		$refs=$this->references->toArray();
		if(count($refs)>0) {
			$string='REFERENCES '.$refs['table'].'('.$refs['column'].')';
			if(!empty($refs['on_update']))
				$string.=' ON UPDATE '.prettify($refs['on_update']);
			if(!empty($refs['on_delete']))
				$string.=' ON DELETE '.prettify($refs['on_delete']);
			$sql.=' '.$string;		
		}
		return $sql;
	}
	
	function toArray() {
		$array=array();
		$array['name']=$this->name;
		$array['type']=$this->type;
		$array['options']=$this->options->toArray();
		$array['references']=$this->references->toArray();
		return $array;
	}
	function __get($var) {
		return $this->$var;
	}
}
?>