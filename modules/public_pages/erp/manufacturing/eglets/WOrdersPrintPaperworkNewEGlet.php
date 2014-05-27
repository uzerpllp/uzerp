<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WOrdersPrintPaperworkNewEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.10 $';
	
	protected $template = 'worders_print_paperwork_list.tpl';

		function populate()
	{
		$worder = new MFWorkorderCollection();
		
		$worder->setParams();
		
		$sh = new SearchHandler($worder,false);
		
		$sh->addConstraint(new Constraint('status', 'in', "('R')"));
		
		$this->setSearchLimit($sh);
				
		$sh->setOrderBy('wo_number');
		
		$worder->load($sh);
		
		$this->contents = $worder;
	}
	
}

// End of WOrdersPrintPaperworkNewEGlet
