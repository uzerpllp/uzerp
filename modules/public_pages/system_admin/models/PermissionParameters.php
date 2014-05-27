<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PermissionParameters extends DataObject {

	function __construct($tablename='permission_parameters') {

		$this->defaultDisplayFields=array('permission'
										 ,'name'
										 ,'value'
										 );
		
		parent::__construct($tablename);

		$this->idField='id';
		$this->identifierField='value';
		
		$this->orderby='name';
		
		$this->hasOne('Permission', 'permissionsid', 'permission');
	}

}
?>
