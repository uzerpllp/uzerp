<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLBudgetYear extends DataObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($tablename='gl_budget_years') {
		
		$this->defaultDisplayFields = array('year');
		
		parent::__construct($tablename);
		$this->idField='id';

	}


}
?>