<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PermissionParametersCollection extends DataObjectCollection {
	
	protected $version = '$Revision: 1.1 $';
	
	public $field;
		
	function __construct($do = 'PermissionParameters', $tablename = 'permission_parameters')
	{
		parent::__construct($do, $tablename);	
	}
	
}

// end of PermissionParametersCollection.php