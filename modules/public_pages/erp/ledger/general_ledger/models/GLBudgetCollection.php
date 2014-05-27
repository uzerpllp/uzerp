<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLBudgetCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	public $field;
	
	function __construct($do='GLBudget', $tablename='glbudgetsoverview') {
		parent::__construct($do, $tablename);

		$this->orderby=array('centre','account');
	}

}
?>