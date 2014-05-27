<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class STItemCollection extends DataObjectCollection {
	
	public $field;
	
	function __construct($do='STItem', $tablename='st_itemsoverview') {
		parent::__construct($do, $tablename);
		$this->identifierField='item_code || \'- \' ||description';
		
	}

}
?>
