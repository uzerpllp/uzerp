<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SalesPerson extends DataObject {

	protected $defaultDisplayFields = array('person'
										   ,'base_commission_rate'
										   );

	function __construct($tablename='sales_people') {
		parent::__construct($tablename);
		$this->idField='id';
		
 		$this->belongsTo('Person', 'person_id', 'person'); 

	}

}
?>