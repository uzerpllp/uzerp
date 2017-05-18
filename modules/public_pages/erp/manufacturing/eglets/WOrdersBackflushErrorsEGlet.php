<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WOrdersBackflushErrorsEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.7 $';
	
	protected $template = 'worders_backflush_errors_list.tpl';
	
	function populate()
	{
		$sttransactions = new STTransactionCollection();
		
		$sttransactions->setParams();
		
		$sh = new SearchHandler($sttransactions,false);
		
		$sh->addConstraint(new Constraint('status', '=', 'E'));
		
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('error_qty', '<', 0));
		$cc->add(new Constraint('qty', '<', 0), 'OR');
		
		$sh->addConstraintChain($cc);
		
		$this->setSearchLimit($sh);
				
		$sh->setOrderBy('created', 'DESC');
		
		$sttransactions->load($sh);
		
		$this->contents = $sttransactions;
		
	}
}

// End of WOrdersBackflushErrorsEGlet
