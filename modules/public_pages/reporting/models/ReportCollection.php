<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ReportCollection extends DataObjectCollection {

	protected $version='$Revision: 1.2 $';	
	public $field;
		
	function __construct($do='Report') {
		parent::__construct($do);
		$this->title='Reports';
	}

}
?>