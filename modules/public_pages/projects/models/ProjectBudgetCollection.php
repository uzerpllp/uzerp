<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ProjectBudgetCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.1 $';
	
	public $field;
		
	function __construct($do='ProjectBudget', $tablename='project_budgets_overview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='name';
	}

}
?>