<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class HasReportCollection extends DataObjectCollection {

	protected $identifierField;
	
	public $field;
		
	function __construct($do='HasReport', $tablename='hasreport_overview') {
		parent::__construct($do, $tablename);
			
		$this->identifierField='description';
	}

}

// End of HasReportCollection