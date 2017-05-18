<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrdersNoAuthUserEGlet extends SimpleListUZlet {

	protected $version='$Revision: 1.9 $';
	
	protected $template = 'porders_auth_requisition.tpl';
	
	function getClassName() {
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$po_obj = new DataObject('po_no_auth_user');		
		
		$po_obj->idField='id';
		
		$po_obj->identifierField='order_number';
		
		$po_col=new DataObjectCollection($po_obj);
		
		$po_col->setParams();
		
		$sh = new SearchHandler($po_col,false);
		
		$sh->setFields(array('id', 'order_number', 'supplier','status'));
		
		$sh->addConstraint(new Constraint('status','!=','X'));
		
		$this->setSearchLimit($sh);
		
		$sh->setOrderby(array('order_date', 'due_date', 'order_number'));
		
		$po_col->load($sh);
		
		$this->contents = $po_col;

	}
}

// End of POrdersNoAuthUserEGlet
