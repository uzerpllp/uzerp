<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PaymentTerm extends DataObject
{

	protected $version = '$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array('description'
											,'basis'
											,'days'
											,'months'
											,'discount'
											,'allow_discount_on_allocation'
											,'pl_discount_glaccount_id'
											,'pl_discount_glcentre_id'
											,'sl_discount_glaccount_id'
											,'sl_discount_glcentre_id'
											);
		
	function __construct($tablename = 'syterms')
	{
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'description';
		
		// Define relationships
		$this->belongsTo('GLAccount', 'pl_discount_glaccount_id', 'pl_discount_glaccount');
		$this->belongsTo('GLCentre', 'pl_discount_glcentre_id', 'pl_discount_glcentre'); 
		$this->belongsTo('GLAccount', 'sl_discount_glaccount_id', 'sl_discount_glaccount');
		$this->belongsTo('GLCentre', 'sl_discount_glcentre_id', 'sl_discount_glcentre');
		
		// Define field defaults
		$this->getField('basis')->setDefault('I');
		
		// Define field formats
		
		// set formatters, more set in load() function
		
		// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('pl_discount_glaccount_id'=>'glaccount_id','pl_discount_glcentre_id'=>'glcentre_id')));
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('sl_discount_glaccount_id'=>'glaccount_id','sl_discount_glcentre_id'=>'glcentre_id')));
		
		$this->validateUniquenessOf('description');
		
		// Define enumerated types
		$this->setEnum('basis',array('I' => 'Invoice'
									,'M' => 'Month'));
	}
	
	function calcSettlementDiscount($_value)
	{
		if ($this->discount > 0)
		{
			return bcmul($_value, bcdiv($this->discount, 100, 4));
		}
		
		return 0;
	}

}

// End of PaymentTerm
