<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HasPermissionCollection extends DataObjectCollection {
	
	public $field;

	function __construct($do='HasPermission', $tablename="haspermissionoverview") {
		parent::__construct($do, $tablename);
	}

	function getPermissions($systemcompany, $roles=null, $orderby='permission') {
		$sh = new SearchHandler($this, false);
		$sh->addConstraint(new Constraint('usercompanyid','=',$systemcompany));
		if (!empty($roles)) {
			$sh->addConstraint(new Constraint('roleid','in','('.$roles.')'));
		}
		$sh->setOrderby($orderby);
		$this->load($sh);
					
	}

}
?>
