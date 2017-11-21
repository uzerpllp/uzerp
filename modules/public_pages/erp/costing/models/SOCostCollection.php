<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class SOCostCollection extends DataObjectCollection {

	protected $version='$Revision: 1.8 $';

	public $field;

	function __construct($do='SOCost', $tablename='so_costsoverview') {
		parent::__construct($do, $tablename);

	}

}
?>
