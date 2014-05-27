<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POProductline extends DataObject
{

	protected $version='$Revision: 1.21 $';
	
	protected $defaultDisplayFields = array(
		'description',
		'supplier',
		'supplier_product_code',
		'glaccount'=>'GL Account',
		'glcentre'=>'GL Centre',
		'stitem'=>'Stock Item',
		'stproductgroup'=>'Product Group',
		'uom_name',
		'start_date',
		'end_date',
		'price',
		'currency',
		'plmaster_id',
		'stitem_id',
		'prod_group_id',
		'productline_header_id'
	);
	
	function __construct($tablename='po_product_lines')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'description';
		$this->orderby			= 'description';
		
		$this->setTitle('PO Product Line');
		
		// Define relationships
		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier');
		$this->belongsTo('Currency', 'currency_id', 'currency');
		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre');
		$this->belongsTo('POProductlineHeader', 'productline_header_id', 'product');
		$this->hasOne('POProductlineHeader', 'productline_header_id', 'product_detail'); 
		
		// Define field formats
		
		// set formatters
		
		// set validators
		
		// Define enumerated types
		
		// set defaults
		$this->getField('price')->setFormatter(new PriceFormatter());
 		$params = DataObjectFactory::Factory('GLParams');
 		$this->getField('currency_id')->setDefault($params->base_currency());
 		
		// Set link rules for 'belongs to' to appear in related view controller sidebar
	
	}

	function getSupplierLines ($supplier, $productsearch = '')
	{

// Returns an array of product line id, product line description
// containing all product lines specific to a customer
// and all other non-specific customer product lines
// that are for items not specific to the customer

// Firstly , get any items specific to the customer
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('plmaster_id', '=', $supplier));
		$cc->add(new Constraint('stitem_id', 'is not', 'NULL'));
		$cc->add($this->currentConstraint($productsearch));
		
		$this->identifierField = 'stitem_id';
		$item_codes = $this->getAll($cc);

// get customer specific product lines
		$cc1 = new ConstraintChain();
		$cc1->add(new Constraint('plmaster_id', '=', $supplier));
		
// Now get the non specific customer product lines
		$cc2 = new ConstraintChain();
		
		$cc2->add(new Constraint('plmaster_id', 'is', 'NULL'));
		
		if (!empty($item_codes))
		{
// There are items specific to the customer
// so get all the other non-customer specific items as well
			$cc3 = new ConstraintChain();
			
			$cc3->add(new Constraint('stitem_id', 'not in', '('.implode(',', $item_codes).')'));
			$cc3->add(new Constraint('stitem_id', 'is', 'NULL'), 'OR');
			
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
		
		return $this->getAll($cc, TRUE, TRUE);

	}
	
	function getNonSPecific ($productsearch = '')
	{

		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('plmaster_id', 'is', 'NULL'));
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

	function getPrice ()
	{
		if (!$this->price && $this->stitem_id)
		{
			$this->loadSTItem($stitem_id);
			
			return $this->item_detail->price;
		}
		else
		{
			return $this->price;
		}
	}
	
	public function getCentres()
	{
		$account = DataObjectFactory::Factory('GLAccount');
		
		$account->load($this->_data['id']);
		
		$centres = $account->getCentres();
	}
	
	function getDefaultProductAccount ()
	{
		$params = DataObjectFactory::Factory('GLParams');
		
		return $params->product_account();
	}

	function getDefaultProductCentre ()
	{
		$params = DataObjectFactory::Factory('GLParams');
		
		return $params->product_centre();
	}

	function getProductGroups ($stitem_id = '')
	{
		if (empty($stitem_id) && $this->isLoaded())
		{
			$stitem_id = $this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			$pg = DataObjectFactory::Factory('STProductgroup');
			
			return $pg->getAll();
		}
		else
		{
			$this->loadSTItem($stitem_id);
			
			return array($this->item_detail->prod_group_id=>$this->item_detail->stproductgroup);
		}
	}

	function getTaxRate ($stitem_id = '')
	{
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id = $this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			return array();
		}
		else
		{
			$this->loadSTItem($stitem_id);
			
			return $this->item_detail->getTaxRate();
		}
	}

	function getUomList ($stitem_id = '')
	{
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id = $this->stitem_id;
		}
		
		$this->loadSTItem($stitem_id);
		
		return $this->item_detail->getUomList();
	}

	function getItem ($stitem_id = '')
	{
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id = $this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			return '';
		}
		else
		{
			$this->loadSTItem($stitem_id);
			
			return $this->item_detail->getIdentifierValue();
		}
	}

	public function currentConstraint($productsearch = '')
	{
		$ccdate = new ConstraintChain();
		
		if (!empty($productsearch))
		{
			$ccdate->add(new Constraint('description', 'like', $productsearch.'%'));
		}
		
		$ccdate->add(new Constraint('start_date', '<=', Constraint::TODAY));
		
		$ccend = new ConstraintChain();
		$ccend->add(new Constraint('end_date', 'is', 'NULL'));
		$ccend->add(new Constraint('end_date', '>=', Constraint::TODAY), 'OR');
		
		$ccdate->add($ccend);
		
		return $ccdate;
	}
	
	/*
	 * Private Functions
	 */
	private function loadSTItem($stitem_id)
	{
		if (!is_null($this->stitem_id) && $this->stitem_id==$stitem_id)
		{
			return;
		}
		
		$this->stitem_id = $stitem_id;
	}

}

// end of POProductline.php
