<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLAccountCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	public $field;
	
	function __construct($do='GLAccount', $tablename='glaccountsoverview') {
		parent::__construct($do, $tablename);

 		$this->identifierField = 'account || \' - \' || description';
	
	}
		
}
?>