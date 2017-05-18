<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLAllocationCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.4 $';
	public $field;
	
	function __construct($do='PLAllocation', $tablename='pl_allocation_details_overview')
	{
		
		parent::__construct($do, $tablename);
		
	}
	
	function remittanceList($trans_id)
	{
		
		$allocation = DataObjectFactory::Factory('PLAllocation');
		
		$allocation->loadBy('transaction_id', $trans_id);
		
		if ($allocation->isLoaded())
		{
			$sh=new SearchHandler($this, false);
			
			$sh->addConstraint(new Constraint('transaction_type', '!=', 'P'));
			$sh->addConstraint(new Constraint('status', '=', 'P'));
			$sh->addConstraint(new Constraint('allocation_id', '=', $allocation->allocation_id));
			
			$sh->setOrderby('transaction_date');
			
			$this->load($sh);		
		}
	}

}

// End of PLAllocationCollection
