<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POQueryInvoicesEGlet extends SimpleListUZlet {

	protected $version = '$Revision: 1.13 $';
	
	protected $template = 'invoice_list.tpl';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		
		$invoices = new PInvoiceCollection;
		$invoices->setParams();
		
		$sh = new SearchHandler($invoices, FALSE);
		
		$sh->addConstraint(new Constraint('status', '=', 'Q'));
		
		$this->setSearchLimit($sh);
		
		$sh->setOrderBy('due_date');
		
		$invoices->load($sh);
		$this->contents = $invoices;
		
		// set vars
		$this->vars['type_label']	= 'Supplier';
		$this->vars['type_field']	= 'supplier';
		$this->vars['module']		= 'purchase_invoicing';
		$this->vars['controller']	= 'pinvoices';
		
	}

}

// end of POQueryInvoicesEGlet.php