<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemSettings extends DataObject {

	protected $version='$Revision: 1.2 $';
	
	protected $defaultDisplayFields = array('system_type'=>'Type'
										   ,'setting_name'=>'Name'
										   ,'setting_value'=>'Value');

	function __construct() {
		parent::__construct('system_settings');
		$this->idField='id';
		
//		$this->validateUniquenessOf(array('system_type_id', 'setting_name'));
 		$this->belongsTo('SystemTypes', 'system_type_id', 'system_type'); 
 		
	}

}
?>
