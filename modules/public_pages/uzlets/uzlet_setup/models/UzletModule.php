<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UzletModule extends DataObject
{
	
	protected $version='$Revision: 1.3 $';

	protected $defaultDisplayFields = array('name');
	
	protected $do;
	
	function __construct($tablename='uzlet_modules')
	{
		parent::__construct($tablename);
		$this->idField='id';
		$this->belongsTo('Uzlet','uzlet_id','uzlet');
		$this->hasOne('ModuleObject','module_id','module');
	}
	
}

// End of UzletModule
