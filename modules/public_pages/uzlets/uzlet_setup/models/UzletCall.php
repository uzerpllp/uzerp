<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UzletCall extends DataObject {
	
	protected $version='$Revision: 1.2 $';

	protected $defaultDisplayFields = array('uzlet_id','module_id');
	
	protected $do;
	
	function __construct($tablename='uzlet_calls') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->belongsTo('Uzlet','uzlet_id','uzlet');
		 		
	}
	
}
?>