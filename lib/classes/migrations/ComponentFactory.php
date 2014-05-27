<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ComponentFactory {


	public static function Factory($string) {
	$map = array (
		'create_table'=>'CreateTableComponent',
		'alter_table'=>'AlterTableComponent',
		'rename_column'=>'RenameColumnComponent',
		'insert_data'=>'InsertDataComponent',
		'sql_query'=>'SQLQueryComponent'
	);
		return $map[$string];
	}

}
?>