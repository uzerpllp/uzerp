<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
//turn a database table into a migration file
/*

-
alter_table:
  name: company
  rename:
    to: companies
    

alter_table:
  name: company
  rename_column:
    name: name
    to: company_name
    

alter_table:
  name: company
  actions:
    -
    add_column:
      name: test
      type: varchar
      options:
        not_null: true
      references:
        table: tests
	column: id
	on_update: cascade
	on_delete: set_null

*/

class MigrationCreator {

	function __construct(&$db) {
		$this->db=$db;
	}
	
	function createFromDatabase($table_list=array(),$include_data=false) {
		$db=$this->db;
		//empty table list means use all
		if(count($table_list)==0) {
			$query='SELECT tablename FROM pg_tables';
			$table_list=$db->GetCol($query);
		}
		$array=array();
		foreach($table_list as $tablename) {
			$array=array_merge($array,$this->createFromTable($tablename,$include_data));
		}
		return $array;
	}
	
	
	function createFromTable($tablename,$include_data=false,$where) {
		//check the table exists
		$db=$this->db;
		$query='SELECT relname FROM pg_class WHERE relname='.$db->qstr($tablename);
		$result=$db->GetOne($query);
		if($result===false)
			throw new Exception('Cannot Create migration from table '.$tablename.' as it doesn\'t exist');
		
		$migration = new Migration();
		$table=$migration->add(new CreateTableComponent());
		$table->name=$tablename;
		$references = $this->GetForeignKeys($tablename);

		$columns=$db->MetaColumns($tablename);
		foreach($columns as $column_data) {
			$column_data->references=$references[$column_data->name];
			$column=new AddColumnComponent($column_data);
			$table->addColumn($column);
		}
		$pks=$db->MetaPrimaryKeys($tablename);
		$table->setPrimaryKeys($pks);
		
		if($include_data) {
			$query='SELECT * FROM '.$tablename.(($where!='')?' WHERE '.$where:'');
			$insert_data = $migration->add(new InsertDataComponent($tablename));
			$insert_data->setDB($db);
			$result=$db->Execute($query) or die($db->ErrorMsg().$query);
			foreach($result as $key=>$row) {
				$insert_data->addRow($row);
			}
		
		}
		
		return $migration->toArray();
	}
	
	private function GetForeignKeys($table, $owner=false, $upper=false) {
		$db=$this->db;
		$sql = 'SELECT t.tgargs as args
		FROM
		pg_trigger t,pg_class c,pg_proc p
		WHERE
		t.tgenabled AND
		t.tgrelid = c.oid AND
		t.tgfoid = p.oid AND
		p.proname = \'RI_FKey_check_ins\' AND	
		c.relname = \''.strtolower($table).'\'
		ORDER BY
			t.tgrelid';
		
		$rs =& $db->Execute($sql);
		
		if ($rs && !$rs->EOF) {
			$arr =& $rs->GetArray();
			$a = array();
			foreach($arr as $v)
			{
			
				$data = explode(chr(0), $v['args']);
				if ($upper) {
					$a[strtoupper($data[2])][] = strtoupper($data[4].'='.$data[5]);
				} else {
					$a[$data[4]]['table'] = $data[2];
					$a[$data[4]]['column'] = $data[5];
					$fks=array();
					$query='SELECT p.proname FROM pg_proc p JOIN pg_trigger t ON (t.tgfoid=p.oid) WHERE t.tgargs LIKE '.$db->qstr('%'.implode('%',$data).'%');
					$triggers=$db->GetCol($query) or die($db->ErrorMsg());
					foreach($triggers as $trigname) {
						switch($trigname) {
							case 'RI_FKey_cascade_del' :
								$a[$data[4]]['on_delete']='cascade';
								break;
							case 'RI_FKey_cascade_upd':
								$a[$data[4]]['on_update']='cascade';
								break;
							case 'RI_FKey_noaction_del':
								$a[$data[4]]['on_delete']='';
								break;
							case 'RI_FKey_noaction_upd':
								$a[$data[4]]['on_update']='';
								break;
							case 'RI_FKey_setnull_upd':
								$a[$data[4]]['on_update']='set_null';
								break;
							case 'RI_FKey_setnull_del':
								$a[$data[4]]['on_delete']='set_null';
								break;
											
						}
					}
				}
			}
			return $a;
		}
		return false;
	}
	
}

?>