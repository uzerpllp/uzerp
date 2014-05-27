<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOProductline extends DataObject
{

	protected $version = '$Revision: 1.31 $';
	
	protected $defaultDisplayFields = array(
		'description',
		'customer',
		'customer_product_code',
		'glaccount'=>'GL Account',
		'glcentre'=>'GL Centre',
		'stitem'=>'Stock Item',
		'stproductgroup'=>'Product Group',
		'uom_name',
		'so_price_type',
		'start_date',
		'end_date',
		'price',
		'currency',
		'taxrate'=>'Tax Rate',
		'slmaster_id',
		'stitem_id',
		'prod_group_id',
		'productline_header_id'
	);

	function __construct($tablename = 'so_product_lines')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'description';
		$this->orderby			= 'description';
		$this->setTitle('SO Product Line');
		
		// Define relationships
		$this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
		$this->belongsTo('Currency', 'currency_id', 'currency');
		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre');
		$this->belongsTo('SOPriceType', 'so_price_type_id', 'so_price_type');
		$this->belongsTo('SOProductlineHeader', 'productline_header_id', 'product');
		$this->hasOne('SOProductlineHeader', 'productline_header_id', 'product_detail'); 
		
		// Define field formats
		
		// set formatters
		
		// set validators
		
		// Define enumerated types
		
		// set defaults
		$params = DataObjectFactory::Factory('GLParams');
 		$this->getField('currency_id')->setDefault($params->base_currency());
 		
		// Set link rules for 'belongs to' to appear in related view controller sidebar
		
	}

	function cb_loaded()
	{
 		$this->getField('price')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
	}

	function delete($id = null, &$errors = array())
	{
		return parent::delete($id, $errors, TRUE);
	}
	
	function getCustomerLines ($customer, $productsearch='')
	{
// Returns an array of product line id, product line description
// containing all product lines specific to a customer
// and all other non-specific customer product lines
// that are for items not specific to the customer

// Firstly , get any items specific to the customer
		$customerdetail = DataObjectFactory::Factory('SLCustomer');
		
		$customerdetail->load($customer);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('slmaster_id','=',$customer));
		$cc->add(new Constraint('stitem_id','is not','NULL'));
		$cc->add($this->currentConstraint($productsearch));
		$this->identifierField='stitem_id';
		$item_codes = $this->getAll($cc, true, true);

// get customer specific product lines
		$cc1 = new ConstraintChain();
		$cc1->add(new Constraint('slmaster_id','=',$customer));
		
// Now get the non specific customer product lines
		$cc2 = new ConstraintChain();
		$cc2->add(new Constraint('slmaster_id','is','NULL'));
		
		if (!$customerdetail || is_null($customerdetail->so_price_type_id))
		{
			$cc2->add(new Constraint('so_price_type_id','is','NULL'));
		}
		else
		{
			$cc2->add(new Constraint('so_price_type_id','=',$customerdetail->so_price_type_id));
		}
		
		if (!empty($item_codes))
		{
// There are items specific to the customer
// so get all the other non-customer specific items as well
			$cc3 = new ConstraintChain();
			$cc3->add(new Constraint('stitem_id','not in','('.implode(',', $item_codes).')'));
			$cc3->add(new Constraint('stitem_id','is','NULL'), 'OR');
			$cc2->add($cc3);
		}
		$cc1->add($cc2, 'OR');
		
// constraint to include all other non-specific product lines
// for items other than use in lines specific to the customer
		$cc = new ConstraintChain();
		$cc->add($this->currentConstraint($productsearch));
		$cc->add($cc1);
		
		$this->identifierField	= 'description';
		$this->orderby			= 'description';
		
		return $this->getAll($cc, true, true);
		
	}
	
	function getCustomerItems ($customer)
	{
// Returns an array of product line id, stock item id
// containing all stock items specific to a customer
// and all other non-specific customer stock items
		
// Firstly , get any items specific to the customer
		$cc = new ConstraintChain();
		
		$cc1 = new ConstraintChain();
		
		$cc1->add(new Constraint('slmaster_id','=',$customer));
		$cc1->add(new Constraint('stitem_id','is not','NULL'));
		
		$cc->add($cc1);
		$cc->add($this->currentConstraint());
		
		$this->identifierField='stitem_id';
		
		$item_codes=$this->getAll($cc1, true, true);

		$cc = new ConstraintChain();
		
		if (!empty($item_codes))
		{
// There are items specific to the customer
// so get all the other non-customer specific items as well
			$cc2 = new ConstraintChain();
			$cc2->add(new Constraint('slmaster_id','is','NULL'));
			$cc3 = new ConstraintChain();
			$cc3->add(new Constraint('stitem_id','not in','('.implode(',', $item_codes).')'));
			$cc2->add($cc3);
			$cc->add($cc1);
			$cc->add($cc2, 'OR');
		}
		else
		{
// No items specific to the customer so get all non-customer specific items
			$cc->add(new Constraint('slmaster_id','is','NULL'));
			$cc->add(new Constraint('stitem_id','is not','NULL'));
		}
		
		$cc->add($this->currentConstraint());
		
		return $this->getAll($cc, true, true);
		
	}
	
	function getNonSpecific ($productsearch='', $_so_price_type_id='')
	{

		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('slmaster_id','is','NULL'));
		
		if (!empty($_so_price_type_id))
		{
			$cc->add(new Constraint('so_price_type_id','=',$_so_price_type_id));
		}
		else
		{
			$cc->add(new Constraint('so_price_type_id','is','NULL'));
		}
		
		$cc->add($this->currentConstraint($productsearch));
		
		return $this->getAll($cc);
		
	}
	
	function getDescription ()
	{
		if (!$this->price && $this->stitem_id)
		{
			$this->loadSTItem($stitem_id);
			
			return $this->item_detail->getIdentifier();
		}
		else
		{
			return $this->description;
		}
	}

	function getGrossPrice ($_stitem_id = '')
	{
		if (!$this->price)
		{
			$price = $this->product_detail->getPrice($_stitem_id);
		}
		else
		{
			$price = $this->price;
		}
		return $price;
	}

	function getPrice ($_prod_group_id='', $_stitem_id='', $_slmaster_id='')
	{
		$price = $this->getGrossPrice($_stitem_id);
		
		$price_discount = $this->getPriceDiscount($_prod_group_id='', $_slmaster_id);
		
		if ($price_discount == 0)
		{
			return $price;
		}
		
		$discount = bcsub(1, bcdiv($price_discount, 100, 5),4);
		
		return bcadd(round($price*$discount, 2), 0);
	}

	function getPriceDiscount ($_prod_group_id='', $_slmaster_id='')
	{

		$_prod_group_id = empty($_prod_group_id)?$this->product_detail->prod_group_id:$_prod_group_id;
		
		if (empty($_slmaster_id) || empty($_prod_group_id))
		{
			return 0;
		}
		return SLDiscount::getDiscount($_slmaster_id, $_prod_group_id);
	}

	public function getUnitSize ()
	{
		return 1;
	}
	
	public function currentConstraint($productsearch='')
	{
		$ccdate = new ConstraintChain();
		
		if (!empty($productsearch))
		{
			$ccdate->add(new Constraint('description','like',$productsearch.'%'));
		}
		
		$ccdate->add(new Constraint('start_date', '<=', Constraint::TODAY));
		
		$ccend = new ConstraintChain();
		
		$ccend->add(new Constraint('end_date', 'is', 'NULL'));
		$ccend->add(new Constraint('end_date', '>=', Constraint::TODAY), 'OR');
		
		$ccdate->add($ccend);
		
		return $ccdate;
	}
	
}

// end of SOProductline.php