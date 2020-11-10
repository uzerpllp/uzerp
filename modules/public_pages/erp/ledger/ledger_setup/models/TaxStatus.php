<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class TaxStatus extends DataObject
{

	protected $defaultDisplayFields = ['description',
									   'apply_tax',
									   'eu_tax',
									   'postponed_vat_accounting' => 'PVA',
									   'reverse_charge'];
	
	function __construct($tablename='tax_statuses')
	{
		parent::__construct($tablename);
		$this->idField='id';
		$this->identifierField = 'description';
		 
		$this->validateUniquenessOf('description');
		
	}

	/**
	 * Return an array of tax status options valid for an SLCustomer
	 *
	 * @return array  Status options
	 */
	public function get_customer_tax_statuses()
	{
		$cc = new ConstraintChain();
		$cc->add(new Constraint('postponed_vat_accounting', 'IS', false));
		$cc->add(new Constraint('reverse_charge', 'IS', false));
		$statuses = $this->getAll($cc);
		return $statuses;
	}

}

// End of TaxStatus
