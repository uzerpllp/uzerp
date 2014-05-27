<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrdersNotAcknowledgedEGlet extends SimpleListUZlet {

	protected $version = '$Revision: 1.10 $';
	
	protected $template = 'porders_list.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$orders = new POrderCollection();
		
		$orders->setParams();
		
		$sh = new SearchHandler($orders,false);
		
		$sh->addConstraint(new Constraint('status', '=', 'O'));
		
		$this->setSearchLimit($sh);
		
		$sh->setOrderBy('due_date');
		
		$orders->load($sh);
		
		$this->contents = $orders;
	}

}

// End of POrdersNotAcknowledgedEGlet
