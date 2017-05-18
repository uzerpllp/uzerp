<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemTypes extends DataObject {

	protected $version='$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('system_type'=>'Type'
										   ,'type_name'=>'Name'
										   ,'description'=>'Description');

	function __construct() {
		parent::__construct('system_types');
		$this->idField='id';
		
		$this->validateUniquenessOf(array('system_type', 'type_name'));
 		
	}

}
?>
