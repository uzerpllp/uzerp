<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class ActivityCollection extends DataObjectCollection {

	public $field;

	function __construct($do='Activity', $tablename='activitiesoverview') {
		parent::__construct($do, $tablename);

		$this->identifierField='name';
	}

}
?>
