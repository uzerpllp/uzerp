<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLSupplierCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.15 $';
	public $field;
	
	function __construct($do='PLSupplier', $tablename='plmaster_overview')
	{
		parent::__construct($do, $tablename);
		
	}
	
	function paymentsList ($supplier_id='')
	{
		$sh=new SearchHandler($this,false);
		
		if (!empty($supplier_id))
		{
			$sh->addConstraint(new Constraint('id', '=', $supplier_id));
		}
		
		$sh->setOrderby('name');
		
		$this->load($sh);

		if ($this)
		{
			foreach ($this as $key=>$supplier)
			{
				$sh = new SearchHandler(new PLTransactionCollection(), false);
				
				$sh->addConstraint(new Constraint('status', '=', 'O'));
				
				$sh->setOrderby(array('due_date'), array('ASC'));
				
				$supplier->addSearchHandler('transactions', $sh);
			}
		}
	}

}

// End of PLSupplierCollection
