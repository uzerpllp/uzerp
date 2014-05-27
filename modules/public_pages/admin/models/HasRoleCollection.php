<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HasRoleCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.6 $';
	
	public $field;
		
	function __construct($do = 'HasRole', $tablename = 'hasrolesoverview')
	{
		parent::__construct($do, $tablename);
			
		$this->identifierField='roleid';
	}
		
}

// End of HasRoleCollection
