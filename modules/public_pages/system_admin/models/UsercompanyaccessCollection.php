<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class UsercompanyaccessCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct($do='Usercompanyaccess', $tablename='user_company_accessoverview') {
			parent::__construct($do, $tablename);
		}

}
?>