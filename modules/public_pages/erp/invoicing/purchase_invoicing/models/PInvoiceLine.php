<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PInvoiceLine extends InvoiceLine {

	protected $version = '$Revision: 1.15 $';
	
	function __construct($tablename = 'pi_lines')
	{

		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField = 'id';
		$this->identifierField	= array('line_number', 'invoice_id');
		
		// Define relationships
		$this->belongsTo('PInvoice', 'invoice_id', 'invoice');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		$this->hasOne('PInvoice', 'invoice_id', 'invoice_detail'); 
		$this->hasOne('POrderLine', 'order_line_id', 'order_line_detail'); 
 		
		// Define field formats
 		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
 		
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));

		// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));
		
		// Define enumerated types

		// Define system defaults
		$this->getField('net_value')->setDefault('0.00');
		$this->getField('tax_value')->setDefault('0.00');
		$this->getField('gross_value')->setDefault('0.00');
		
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

	public function delete()
	{

		$flash = Flash::Instance();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$result = parent::delete();
		
		// Save the header to update the header totals
		if ($result && !$this->invoice_detail->save())
		{
			$result = FALSE;
			$flash->addError('Error updating header');
		}
		
		if ($result)
		{
			// Check if invoice line is linked to received line
			$poreceivedline = DataObjectFactory::Factory('POReceivedline');
			
			$poreceivedline->loadBy(array('order_id', 'orderline_id'), array($this->purchase_order_id, $this->order_line_id));
			
			if ($poreceivedline->isLoaded())
			{
				// Invoice line is linked to received line so break the link
				$poreceivedline->invoice_id = $poreceivedline->invoice_number = NULL;
				
				if (!$poreceivedline->save())
				{
					$result = FALSE;
					$flash->addError('Error updating received line');
				}
			}
		}
		
		if ($result)
		{
			
			// Now update the line numbers of following lines
			$pinvoicelines = new PInvoiceLineCollection($this);
			
			$sh = new SearchHandler($pinvoicelines, FALSE);
			$sh->addConstraint(new Constraint('invoice_id', '=', $this->invoice_id));
			$sh->addConstraint(new Constraint('line_number', '>', $this->line_number));
			
			if ($pinvoicelines->update('line_number', '(line_number-1)', $sh) === FALSE)
			{
				$flash->addError('Error updating line numbers '.$db->ErrorMsg());
				$result = FALSE;
			}
			
		}
		
		if ($result === FALSE)
		{
			$db->FailTrans();
		}			
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public static function Factory(PInvoice $header, $line_data, &$errors)
	{
		
		if (empty($line_data['invoice_id']))
		{
			$line_data['invoice_id'] = $header->id;
		}
		
		if (empty($line_data['line_number']))
		{
			$line_data['line_number'] = $header->getNextLineNumber();
		}
		
		if ($line_data['net_value'] > 0)
		{
			// do nothing
		}
		else
		{
			$errors[] = 'Zero net value';
			return FALSE;
		}
		
		$line_data['invoice_id'] = $header->id;
		
		if (empty($line_data['description']))
		{
			$line_data['description'] = $line_data['item_description'];
		}
		
		$line_data['tax_status_id']		= $header->tax_status_id;
		$line_data['currency_id']		= $header->currency_id;
		$line_data['rate']				= $header->rate;
		$line_data['twin_currency_id']	= $header->twin_currency_id;
		$line_data['twin_rate']			= $header->twin_rate;
		
		$line_data['glaccount_centre_id']=GLAccountCentre::getAccountCentreId($line_data['glaccount_id'], $line_data['glcentre_id'], $errors);
		
		return parent::makeLine($line_data, 'PInvoiceLine', $errors);

	}
	
	public function save($pinvoice = null)
	{

		$db = DB::Instance();
		$db->startTrans();
		
		$result = parent::save();
		
		if ($result && !is_null($pinvoice))
		{
			// Need to update the header totals
			$result = $pinvoice->save();
		}
		
		if ($result === FALSE)
		{
			
			$flash = Flash::Instance();
			$flash->addError($db->errorMsg());
			
			$db->FailTrans();
			
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public function sortOutValues($data) {}	
	
	public function transaction_type($transaction_type)
	{
		$invoice = DataObjectFactory::Factory('PInvoice');
		return $invoice->getEnum('transaction_type', $transaction_type);
	}
	
}

// end of PInvoiceLine.php
