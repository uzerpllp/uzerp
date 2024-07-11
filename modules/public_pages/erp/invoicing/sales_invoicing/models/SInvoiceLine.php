<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SInvoiceLine extends InvoiceLine {

	protected $version = '$Revision: 1.22 $';
	
	protected $defaultsNotAllowed = array(
		'line_number',
		'rate',
		'base_net_value',
		'twin_net_value',
		'twin_currency_id',
		'twin_rate'
	);
	
	function __construct($tablename = 'si_lines')
	{
		
		// Register non-persistent attributes
		$this->setAdditional('product_search');

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('line_number', 'invoice_id');
		
		// Define relationships
		$this->belongsTo('SInvoice', 'invoice_id', 'invoice');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 
		$this->belongsTo('STuom', 'stuom_id', 'uom_name'); 
		$this->belongsTo('SOProductline', 'productline_id', 'product_description'); 
		$this->belongsTo('SOrder', 'sales_order_id', 'sales_order'); 
		$this->belongsTo('SOrderLine', 'order_line_id', 'order_line_number'); 
 		$this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate'); 
		$this->hasOne('TaxRate', 'tax_rate_id', 'tax_rate_detail'); 
		$this->hasOne('STItem', 'stitem_id', 'item_detail'); 
		$this->hasOne('SInvoice', 'invoice_id', 'invoice_detail'); 
		$this->hasOne('SOrderLine', 'order_line_id', 'order_line_detail'); 
				
		// Define enumerated types
		
		// Define system defaults
		$this->getField('sales_qty')->setDefault('0');
		$this->getField('sales_price')->setDefault('0.00');
		
		// Define field formats		
		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();

		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));
		
		// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));
		
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
		$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('twin_net_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_tax_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_gross_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		
	}
	
	public function delete($id = \null, &$errors = array(), $archive = \FALSE, $archive_table = \null, $archive_schema = \null)
	{

		$flash = Flash::Instance();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$result = parent::delete();
		
		// Save the header to update the header totals
		if ($result && !$this->invoice_detail->save())
		{
			$result = false;
			$flash->addError('Error updating header');
		}
		
		if ($result)
		{
			
			// Now update the line numbers of following lines
			$sinvoicelines = new SInvoiceLineCollection($this);
			
			$sh = new SearchHandler($sinvoicelines, false);
			$sh->addConstraint(new Constraint('invoice_id', '=', $this->invoice_id));
			$sh->addConstraint(new Constraint('line_number', '>', $this->line_number));
			
			if ($sinvoicelines->update('line_number', '(line_number-1)', $sh) === false)
			{
				$flash->addError('Error updating line numbers ' . $db->ErrorMsg());
				$result = false;
			}
			
		}
		
		if ($result === false)
		{
			$db->FailTrans();
		}			
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public static function SInvoiceLineFactory(SInvoice $header, $line_data, &$errors = [])
	{
		
		if (empty($line_data['invoice_id']))
		{
			$line_data['invoice_id'] = $header->id;
		}
		
		if (empty($line_data['line_number']))
		{
			$line_data['line_number'] = $header->getNextLineNumber();
		}
		
		if ($line_data['sales_qty'] > 0 && $line_data['sales_price'] > 0)
		{
			// do nothing
		}
		else
		{
			$errors[] = 'Zero quantity or net value';
			return false;
		}
		
		$line_data['invoice_id'] = $header->id;
		
		if (!isset($line_data['move_stock']))
		{
			$line_data['move_stock'] = 'f';
		}
		
		if ($line_data['productline_id'] == -1)
		{
			$line_data['productline_id']	= '';
			$line_data['stitem_id']			= '';
		}
		elseif ($line_data['productline_id'] > 0)
		{
			
			$productline = DataObjectFactory::Factory('SOProductline');
			$productline->load($line_data['productline_id']);
			
			if ($productline && $productline->isLoaded())
			{
				$productlineheader				= $productline->product_detail;
				$line_data['stitem_id']			= $productlineheader->stitem_id;
				$line_data['item_description']	= $productlineheader->stitem;
			}
			else
			{
				$line_data['stitem_id'] = '';
			}
			
			if (!empty($line_data['stitem_id']))
			{
				
				$stitem = DataObjectFactory::Factory('STitem');
				$stitem->load($line_data['stitem_id']);
				
				if ($stitem)
				{
					$line_data['item_description']	= $stitem->getIdentifierValue();
					$line_data['stuom_id']			= $stitem->uom_id;
				}
				
			}
		
		}
		
		if (empty($line_data['description']))
		{
			$line_data['description'] = $line_data['item_description'];
		}
		
		$line_data['tax_status_id']			= $header->tax_status_id;
		$line_data['currency_id']			= $header->currency_id;
		$line_data['rate']					= $header->rate;
		$line_data['twin_currency_id']		= $header->twin_currency_id;
		$line_data['twin_rate']				= $header->twin_rate;
		$line_data['settlement_discount']	= $header->payment_term->calcSettlementDiscount($line_data['net_value']);
		
		$line_data['glaccount_centre_id']	= GLAccountCentre::getAccountCentreId($line_data['glaccount_id'], $line_data['glcentre_id'], $errors);
		
		return parent::makeLine($line_data, 'SInvoiceLine', $errors);

	}
	
	public function save($sinvoice = null)
	{

		$db = DB::Instance();
		$db->startTrans();
		
		$result = parent::save();
		
		if ($result && !is_null($sinvoice))
		{
			// Need to update the header totals
			$result = $sinvoice->save();
		}
		
		if ($result === false)
		{
			
			$flash = Flash::Instance();
			$flash->addError($db->errorMsg());
			
			$db->FailTrans();
			
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public function sortOutValues($data)
	{
		
		//net value is unit-price * quantity
		$this->net_value = round(bcmul($this->sales_qty, $this->sales_price, 4), 2);
		
		//tax  (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
		//this function is a wrapper to a call to a config-dependent method
		$tax_percentage			= calc_tax_percentage($data['tax_rate_id'], $data['tax_status_id'], $this->net_value);
		$this->tax_percentage	= $tax_percentage;
		
		//tax_value is the tax percentage of the net value
		$this->tax_value = trunc(bcmul($this->net_value, $tax_percentage), 2);
		
	}

	public function transaction_type($transaction_type)
	{
		$invoice = DataObjectFactory::Factory('SInvoice');
		return $invoice->getEnum('transaction_type', $transaction_type);
	}
	
}

// end SInvoiceLine.php
