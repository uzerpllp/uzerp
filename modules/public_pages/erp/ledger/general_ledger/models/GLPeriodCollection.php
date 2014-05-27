<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLPeriodCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.4 $';
	public $field;
	
	function __construct($do='GLPeriod', $tablename='gl_periods') {
		parent::__construct($do, $tablename);
		$this->orderby = array('year', 'period');
		$this->direction = array('DESC', 'DESC');
		$this->_identifierField = 'year || \' - period \' || period';
	}
		
}
?>