<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLBudgetYearCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.3 $';
	public $field;
	
	function __construct($do='GLBudgetYear', $tablename='gl_budget_years') {
		parent::__construct($do, $tablename);
		
	}


	
}
?>