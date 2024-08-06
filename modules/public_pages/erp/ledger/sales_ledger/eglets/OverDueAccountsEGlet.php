<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class OverDueAccountsEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.9 $';

	public $template = 'overdue_accounts.tpl';

	function populate()
	{
		$invoices = new SInvoiceCollection();

		$pl = new PageList('overdue_accounts');

		// TODO: this returns a collection; needs to return an array
		$invoices->getOverdueInvoices();

		$customerlist = array();

		foreach ($invoices as $invoice)
		{
			$customerlist[$invoice->slmaster_id] = $invoice->customer;
		}

		$customers = new SLCustomerCollection();

		$customers->setParams();

		$sh = new SearchHandler($customers, false);

		if (count($customerlist)>0)
		{
	 		$sh->addConstraint(new Constraint('id', 'in', '('.implode(',',array_keys($customerlist)).')'));
		}
		else
		{
			$sh->addConstraint(new Constraint('id', '=', '0'));
		}

		$this->setSearchLimit($sh);

		$customers->load($sh);

		$this->contents = $customers;
	}
}

// End of OverDueAccountsEGlet
