<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SystemPolicyAccessListCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.1 $';
	
	public $field;
		
	function __construct($do='SystemPolicyAccessList', $tablename = 'sys_policy_access_lists_overview')
	{
		
		parent::__construct($do, $tablename);
			
	}

}

// End of SystemPolicyAccessListCollection
