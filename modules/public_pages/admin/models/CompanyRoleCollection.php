<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CompanyRoleCollection extends DataObjectCollection {
	
	public $field;
		
	function __construct($do='CompanyRole', $tablename='companyrolesoverview') {
		parent::__construct($do, $tablename);
	}

}
?>
