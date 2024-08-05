<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POoverdueInvoicesEGlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.12 $';

	protected $template = 'invoice_list.tpl';

	function getClassName()
	{
		return 'eglet double_eglet';
	}

	function populate()
	{

		$invoices = new PInvoiceCollection();
		$invoices->setParams();

		$sh = new SearchHandler($invoices,false);

		$sh->addConstraint(new Constraint('status', '=', 'O'));
		$sh->addConstraint(new Constraint('due_date', '<=', fix_date(date(DATE_FORMAT))));

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

// end of POoverdueInvoicesEGlet.php