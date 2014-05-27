<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PPOverdueEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.6 $';
	
	protected $template = 'pp_list.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$pp = new PeriodicPaymentCollection();
		
		$pl = new PageList('overdue_periodic_payments');
		
		$sh = new SearchHandler($pp,false);
		
		$sh->addConstraint(new Constraint('status', '=', "('A')"));
		$sh->addConstraint(new Constraint('next_due_date', '<=', fix_date(date(DATE_FORMAT))));
		
		$this->setSearchLimit($sh);
		
		$sh->setOrderBy('next_due_date');
		
		$pp->load($sh);
		
		$this->contents = $pp;
	}
	
}

// End of PPOverdueEGlet
