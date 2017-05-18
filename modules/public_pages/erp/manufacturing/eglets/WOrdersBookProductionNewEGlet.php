<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WOrdersBookProductionNewEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.12 $';
	
	protected $template = 'worders_book_completed_list.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$worder = new MFWorkorderCollection();
		
		$worder->setParams();
		
		$sh = new SearchHandler($worder,false);
		
		$sh->addConstraint(new Constraint('status', 'in', "('R', 'O')"));
		
		$this->setSearchLimit($sh);
				
		$sh->setOrderBy('wo_number');
		
		$worder->load($sh);
		
		$this->contents = $worder;
	}
	
}

// End of WOrdersBookProductionNewEGlet
