<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CompanypermissionCollection extends DataObjectCollection {
	
		public $field;
		
		function __construct($do='Companypermission', $tablename='companypermissionsoverview') {
			parent::__construct($do, $tablename);
			
			$this->identifierField='permission';
		}
	
		function getCompanies($permissionid) {
			$sh = new SearchHandler($this, false, false);
			$sh->addConstraint(new Constraint('permissionid','=',$permissionid));
			$sh->setOrderby('name');
			$this->load($sh);
						
		}
	
		function getPermissions($systemcompany, $orderby='permission') {
			$sh = new SearchHandler($this, false, false);
			$sh->addConstraint(new Constraint('usercompanyid','=',$systemcompany));
			$sh->setOrderby($orderby);
			$this->load($sh);
						
		}
		
		function getPermissionIDs($systemcompany, $orderby='permission') {
			
			$sh = new SearchHandler($this, false, false);
			$sh->setFields(array('permissionid', 'permission'));
			$sh->addConstraint(new Constraint('usercompanyid','=',$systemcompany));
			$sh->setOrderby($orderby);
			$this->load($sh);
			return $this->getAssoc();
						
		}
		
}
?>
