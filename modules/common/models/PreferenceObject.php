<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class PreferenceObject extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	public function __construct($tablename='userpreferences') {
		parent::__construct($tablename);
		$this->idField='id';
	}
	
}
?>