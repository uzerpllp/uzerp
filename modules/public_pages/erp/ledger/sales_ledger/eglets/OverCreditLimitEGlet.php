<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OverCreditLimitEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.10 $';
	
	public $template = 'over_credit_limit.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
		
	function populate()
	{
		$customers = new SLCustomerCollection();
		
		$customers->setParams();
		
		$sh = new SearchHandler($customers, false);
		
		$sh->addConstraint(new Constraint('credit_limit', '<', '(outstanding_balance)'));
		
		$this->setSearchLimit($sh);
		
		$customers->load($sh);
		
		$this->contents = $customers;
	}
}

// End of OverCreditLimitEGlet
