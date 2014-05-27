<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrdersAuthRequisitionEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.11 $';
	
	protected $template = 'porders_auth_requisition.tpl';

	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$po_obj = new DataObject('po_auth_summary');
				
		$po_obj->idField		 = 'id';
		$po_obj->identifierField = 'order_number';
		
		$po_col = new DataObjectCollection($po_obj);
		
		$po_col->setParams();
		
		$sh = new SearchHandler($po_col, false);
		
		$sh->setFields(array('id', 'order_number','username', 'supplier', 'status'));
		
		$sh->addConstraint(new Constraint('username', '=', EGS_USERNAME));
		$sh->addConstraint(new Constraint('status', '!=', 'X'));
		$sh->addConstraint(new Constraint('type', '=', 'R'));
		
		$this->setSearchLimit($sh);
				
		$sh->setOrderby(array('order_date', 'due_date', 'order_number'));
		
		$po_col->load($sh);
		
		$this->contents = $po_col;
	}

}

// End of POrdersAuthRequisitionEGlet
