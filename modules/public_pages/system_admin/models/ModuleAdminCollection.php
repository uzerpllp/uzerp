<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModuleAdminCollection extends DataObjectCollection {

	function __construct($do='ModuleAdmin', $tablename='moduleadminsoverview') {
		parent::__construct($do, $tablename);

	}

}
?>