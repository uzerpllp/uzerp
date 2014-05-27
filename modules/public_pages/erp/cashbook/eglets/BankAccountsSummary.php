<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class BankAccountsSummary extends SimpleListUZlet
{

	protected $version = '$Revision: 1.4 $';

	protected $template = 'bank_accounts_summary.tpl';
	
	function populate()
	{
		$pl = new PageList('bank_accounts');
		
		$cbaccounts = new CBAccountCollection();
		
		$sh = new SearchHandler($cbaccounts,false);
		
		$this->setSearchLimit($sh);
		
		$sh->setOrderBy('name');
		
		$cbaccounts->load($sh);
		
//		$pl->addFromCollection($cbaccounts,array('module'=>'cashbook','controller'=>'bankaccounts','action'=>'view'),array('id'),'','name');
//		$this->contents=$pl->getPages()->toArray();
		$this->contents = $cbaccounts;
	}

	
}

// End of BankAccountsSummary
