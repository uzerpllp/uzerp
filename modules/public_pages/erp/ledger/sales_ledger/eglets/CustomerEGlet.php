<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CustomerEGLet extends SimpleListEGlet {

	protected $version='$Revision: 1.5 $';

//	protected $template='company_selector.tpl';
	function populate() {
		$pl = new PageList('my_customers');
		$customers = new SLCustomerCollection(new SLCustomer);
//	Either get data from a function that returns a collection
		//		$customers->getUnassignedCompanies();
// Or construct a collection from a sql query
 		$db = DB::Instance();
		$query = 'select id, * from slmaster where usercompanyid='.EGS_COMPANY_ID.' limit 10';
		$results = $db->getAssoc($query);
		foreach ($results as $id=>$row) {
			$customer = new SLCustomer();
			$customer->_data = $row;
			$customer->load($id);
			$customer->id = $id;
			$customers->add($customer);
		}
		$pl->addFromCollection($customers,array('module'=>'sales_ledger','controller'=>'slcustomers','action'=>'view'),array('id'),'','name');
		$this->contents=$pl->getPages()->toArray();
	}

	
}
?>
