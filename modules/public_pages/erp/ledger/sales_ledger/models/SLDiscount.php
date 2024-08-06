<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SLDiscount extends DataObject {

	protected $version='$Revision: 1.8 $';

	protected $defaultDisplayFields = array('customer'
										   ,'product_group'
										   ,'discount_percentage');

	function __construct($tablename='sl_discounts') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField='id';
		$this->identifierField = array('customer', 'product_group');

		$this->orderby=array('customer' ,'product_group');

// Define relationships
		$this->belongsTo('SLCustomer', 'slmaster_id', 'customer');

		$pg_filter = new ConstraintChain();
		$pg_filter->add(new Constraint('active', 'is', true));
		$this->belongsTo('STProductgroup', 'prod_group_id', 'product_group', $pg_filter);

// Define enumerated types

// Define system defaults

// Define validation
		$this->validateUniquenessOf(array('slmaster_id', 'prod_group_id')); 

	}

	static function getDiscount ($slmaster, $prod_group) {
		$cc=new ConstraintChain();
		$cc->add(new Constraint('slmaster_id', '=', $slmaster));
		$cc->add(new Constraint('prod_group_id', '=', $prod_group));
		$sldiscount=new SLDiscount();
		$sldiscount->loadBy($cc);
		if ($sldiscount->isLoaded()) {
			return $sldiscount->discount_percentage;
		} else {
			return 0;
		}
	}

	static function unassignedProductGroups($_slmaster_id = '')
	{

		if (empty($_slmaster_id))
		{
			$current_prod_groups = array();
		}
		else
		{
			$cc=new ConstraintChain();
			$cc->add(new Constraint('slmaster_id', '=', $_slmaster_id));

			$sldiscount=new SLDiscount();
			$sldiscount->identifierField = 'prod_group_id';
			$sldiscount->orderby = 'prod_group_id';
			$current_prod_groups = $sldiscount->getAll($cc);
		}

		$cc=new ConstraintChain();

		$prodgroup = new STProductgroup();

		if (count($current_prod_groups)>0)
		{
			$cc->add(new Constraint($prodgroup->idField, 'not in', '('.implode(',', $current_prod_groups).')'));
		}

		return $prodgroup->getAll($cc);

	}

	function getIdentifierValue()
	{
		return 'SL Discount for '.$this->customer;
	}

}

// End of SLDiscount
