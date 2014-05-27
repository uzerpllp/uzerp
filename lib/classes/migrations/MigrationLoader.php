<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SQLQueryComponent extends MigrationComponent {
	protected $query;
	public function __construct() {
		$this->mig_type='sql_query';
	}
	function Factory($data) {
		$sql_query=new SQLQueryComponent();
		$sql_query->query=$data['query'];
		return $sql_query;
	}
	function toSQL() {
		return $this->query;
	}
	function toArray() {
		return array('query'=>$this->query);
	}
	function __set($key,$val) {
		if($key=='query')
			return $this->$key;
	}
}

class MigrationLoader {
	
	function __construct($db) {
		$this->db=$db;
	}
	
	function loadFromArray($array,$ignore=array()) {
		$sql="--outputting sql\n";
		$sql.="BEGIN;\n";
		foreach($array as $data) {
		
			if(!in_array($data['type'],$ignore)) {
			
				$component_name=ComponentFactory::Factory($data['type']);
				$component =call_user_func(array($component_name,'Factory'),$data);
				$sql.=$component->toSQL();
			}
			else {
			
			}
		}
		$sql.='COMMIT;';
		return $sql;
	}

}
?>