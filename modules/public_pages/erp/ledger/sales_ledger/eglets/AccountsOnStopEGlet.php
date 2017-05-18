<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class AccountsOnStopEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.7 $';
	
	public $template = 'slcustomer_list.tpl';
	
	function populate()
	{
		$customers = new SLCustomerCollection();
		
		$customers->setParams();
		
		$sh = new SearchHandler($customers, false);
		
		$sh->addConstraint(new Constraint('account_status', '=', 'S'));
		
		$this->setSearchLimit($sh);
		
		$customers->load($sh);
		
		$this->contents = $customers;
	}
}

// End of AccountsOnStopEGlet
