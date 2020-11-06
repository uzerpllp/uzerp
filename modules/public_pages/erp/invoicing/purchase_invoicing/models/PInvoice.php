<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PInvoice extends Invoice
{

	protected $version = '$Revision: 1.26 $';
	
	protected $defaultDisplayFields = array(
		'invoice_number',
		'supplier',
		'invoice_date',
		'ext_reference' => 'Supplier Reference',
		'transaction_type',
		'status',
		'gross_value',
		'currency',
		'base_gross_value',
//	'project',
		'project_id',
		'plmaster_id'
	);
	
	function __construct($tablename = 'pi_header')
	{
		
		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'invoice_number';
		
		// Set ordering attributes
		$this->orderby	= array('invoice_date', 'invoice_number');
		$this->orderdir	= array('DESC', 'DESC');
				
		$this->validateUniquenessOf('invoice_number');
 		
		// Define relationships
		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier');
 		$this->belongsTo('User', 'auth_by', 'auth');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('PaymentTerm', 'payment_term_id', 'payment'); 
 		$this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status');
		$this->belongsTo('Project', 'project_id', 'project');
		$this->belongsTo('Task', 'task_id', 'task'); 		
 		$this->hasMany('PInvoiceLine','lines','invoice_id');
		$this->hasMany('POReceivedLine','grn_lines','invoice_id');
		$this->hasOne('PaymentTerm', 'payment_term_id', 'payment_term');
		
		// Define field formats
		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();

		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('transaction_type')->setDefault('I');
 		
		// Define enumerated types
 		$this->setEnum(
 			'transaction_type',
 			array(
 				'I'	=> 'Invoice',
				'C'	=> 'Credit Note',
				'T'	=> 'Template'
				)
		);
		
		$this->setEnum(
			'status',
			array(
				'N'	=> 'New',
				'O'	=> 'Open',
				'Q'	=> 'Query',
				'P'	=> 'Paid'
			)
		);
		
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
		$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('settlement_discount')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		$this->getField('twin_net_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_tax_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		$this->getField('twin_gross_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
		
	}

	public static function Factory($header_data, &$errors)
	{
		
		$supplier = DataObjectFactory::Factory('PLSupplier');
		$supplier = $supplier->load($header_data['plmaster_id']);
		
		if ($supplier)
		{
			$header_data['currency_id']		= $supplier->currency_id;
			$header_data['payment_term_id']	= $supplier->payment_term_id;
			$header_data['tax_status_id']	= $supplier->tax_status_id;
		}
		
		$header = Invoice::makeHeader($header_data, 'PInvoice', $errors);
		
		if( $header !== false)
		{
			//$line_data['tax_status_id']=$header->tax_status_id;
			return $header;
		}
		
		return false;
		
	}

	public static function getInvoices($purchase_order_number)
	{
		
		$pi_lines					= DataObjectFactory::Factory('PInvoiceLine');
		$pi_lines->idField			= 'invoice_id';
		$pi_lines->identifierField	= 'order_number';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('order_number', '=', $purchase_order_number));
		
		return $pi_lines->getAll($cc, true, true);
		
	}
	
	public function getNextLineNumber()
	{
		$pinvoiceline = DataObjectFactory::Factory('PInvoiceLine');
		return parent::getNextLineNumber($pinvoiceline);
	}
	
	public function getOrderNumbers()
	{
		
		$pi_lines					= DataObjectFactory::Factory('PInvoiceLine');
		$pi_lines->idField			= 'order_number';
		$pi_lines->identifierField	= 'invoice_id, purchase_order_id';
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('invoice_id', '=', $this->id));
		$cc->add(new Constraint('purchase_order_id', 'is not', 'NULL'));
		
		return $pi_lines->getAll($cc, true, true);
		
	}

	public function newStatus()
	{
		return 'N';
	}
	
	public function openStatus()
	{
		return 'O';
	}
	
	public function queryStatus()
	{
		return 'Q';
	}
	
	public function paidStatus() {
		return 'P';
	}
	
	public function post(&$errors = array())
	{
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$result = parent::post($errors);
		
		if ($result)
		{
			$receivedlines = new POReceivedLineCollection(DataObjectFactory::Factory('POReceivedLine'));
			
			$sh = new SearchHandler($receivedlines, FALSE);
			
			$sh->addConstraint(new Constraint('invoice_id', '=', $this->{$this->idField}));
			
			$result = $receivedlines->update('status', 'I', $sh);
			
			if ($result === FALSE)
			{
				$errors[] = 'Error updating GRN status';
			}
		}
		
		if ($result === FALSE)
		{
			$errors[] = $db->ErrorMsg();
			$db->FailTrans();
		}
		
		$db->completeTrans();
		
		return $result;
	}
	
	public function save ()
	{

		$pi_line = DataObjectFactory::Factory('PInvoiceLine');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('invoice_id', '=', $this->id));		
		
		$totals = $pi_line->getSumFields(
			array(
				'gross_value',
				'tax_value',
				'net_value',
				'twin_gross_value',
				'twin_tax_value',
				'twin_net_value',
				'base_Gross_value',
				'base_tax_value',
				'base_net_value',
			),
			$cc,
			'pi_lines'
		);
				
		unset($totals['numrows']);
				
		// set the correct totals back to the invoice header
		foreach($totals as $field => $value)
		{
			$this->$field = (empty($value))?'0.00':bcadd($value,0);
		}

		if ($this->settlement_discount == 0)
		{
			$this->settlement_discount = bcadd($this->getSettlementDiscount(), 0);
		}
		
		return parent::save();
	}
	
	public function save_model($data)
	{
// Used to save Invoice Header and Invoice Lines from import or copy of existing
		$flash = Flash::Instance();

		if (empty($data['PInvoice']) || empty($data['PInvoiceLine']))
		{
			$flash->addError('Error trying to save invoice');
			return false;
		}
		
		$errors = array();
		
		$db=DB::Instance();
		$db->StartTrans();
		
		$header = $data['PInvoice'];

		$lines_data = DataObjectCollection::joinArray($data['PInvoiceLine'], 0);
		
		if (!$lines_data || empty($lines_data))
		{
			$lines_data[] = $data['PInvoiceLine'];
		}

		$invoice = PInvoice::Factory($header,$errors);
		
		if (!$invoice || count($errors)>0)
		{
			$errors[] = 'Invoice validation failed';
		}
		elseif (!$invoice->save())
		{
			$errors[] = 'Invoice creation failed';
		}
		
		if ($invoice)
		{
			foreach ($lines_data as $line)
			{
				$line['invoice_id'] = $invoice->{$invoice->idField};
				
				$invoiceline = PInvoiceLine::Factory($invoice, $line, $errors);
				
				if (!$invoiceline || count($errors)>0)
				{
					$errors[] = 'Invoice Line validation failed for line '.$line['line_number'];
				}
				elseif (!$invoiceline->save())
				{
					$errors[] = 'Invoice Line creation failed for line '.$line['line_number'];
				}			
			}
		}
		
		if (count($errors)===0)
		{
			if (!$invoice->save())
			{
				$errors[] = 'Error updating Invoice totals';
			}
			else
			{
				$result = array('internal_id'=>$invoice->{$invoice->idField}, 'internal_identifier_field'=>$invoice->identifierField, 'internal_identifier_value'=>$invoice->getidentifierValue());
			}
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$db->FailTrans();
			$result=false;
		}
		
		$db->CompleteTrans();
		
		return $result;

	}
	
	public function transactionFactory()
	{
		$db = DB::Instance();
		
		$transaction = DataObjectFactory::Factory('PLTransaction');
		
		$transaction->{$transaction->idField} = $db->GenID('PLTransactions_id_seq');
		
		return $transaction;
	}
	
	protected function get_ledger_control_account($gl_params = null, &$errors=array())
	{
		
		if (!($gl_params instanceof GLParams))
		{
			$gl_params = DataObjectFactory::Factory('GLParams');
		}
		
		$glaccount_id = $gl_params->purchase_ledger_control_account();
		
		if ($glaccount_id===false)
		{
			$errors[]='Ledger Control Account Code not found';
		}
		
		return $glaccount_id;
	
	}
	
	protected function makeGLTransactionLines($gl_data, &$gl_transactions, &$errors = array())
	{
		$newerrors = array();
		
		// EU Acquisitions only applies to Purchases
		$tax_status = DataObjectFactory::Factory('TaxStatus');
		
		if ($tax_status->load($this->tax_status_id))
		{
			$eu_acquisition = ($tax_status->eu_tax == 't');
			$vat_postponed = ($tax_status->postponed_vat_accounting == 't');
			$reverse_charge = ($tax_status->reverse_charge == 't');
		}
		else
		{
			$errors[] = 'Error getting Tax Status for the Invoice';
			return FALSE;
		}
		
		if ($eu_acquisition || $vat_postponed || $reverse_charge)
		{
			$eu_gl_data				 = $gl_data;
			$eu_gl_data['value']	 = 0;
			$eu_gl_data['twinvalue'] = 0;
		}
		
		// Get tax rates
		$tax_rate = DataObjectFactory::Factory('TaxRate');
		
		$tax_rate->identifierField = 'percentage';
		
		$tax_rates = $tax_rate->getAll();
		
		$gl_params = DataObjectFactory::Factory('GLParams');
		
		$accruals_control_account	= $gl_params->accruals_control_account();
		$balance_sheet_cost_centre	= $gl_data['glcentre_id'];
		
		foreach($this->lines as $line)
		{
			
			// Set common gl data for the line
			$line->makeGLTransactions($gl_data);
						
			if ($accruals_control_account)
			{
				// Need to check if there is a GRN for this line
				// and whether it is accrued and accrual accounting is enabled
				$poreceivedline = DataObjectFactory::Factory('POReceivedLine');
					
				$poreceivedline->load($line->grn_id);
					
				if ($poreceivedline->isLoaded())
				{
					if ($poreceivedline->net_value != $line->net_value)
					{
						// Invoice line value differs from received (quantity*price) value
						
						// Convert received line net value to base
						$base_net_value = $poreceivedline->net_value;
						
						if ($line->rate <> 1)
						{
							// Convert to base value
							$base_net_value = round(bcmul($line->rate, $base_net_value, 4), 2);
						}
						
						$gl_data['base_net_value']	= bcsub($line->base_net_value, $base_net_value);
						// Need to get the twin value for this value
						$gl_data['twin_net_value']	=  round(bcmul($line->twin_rate, $gl_data['base_net_value'], 4), 2);
						
						$original_comment	= $gl_data['comment'];
						$gl_data['comment']	= $gl_data['comment'] . ' - Invoice Variance';
						
						// Make the GL Transaction for the variance of the invoice line against GRN
						// The variance is posted to the invoice line GL Account/Centre
						$element = GLTransaction::makeCBLine($gl_data, $newerrors);
						
						if($element!==FALSE)
						{
							$gl_transactions[]=$element;
						}
						else
						{
							$errors+=$newerrors;
							return FALSE;
						}
						
						$gl_data['comment']			= $original_comment;
						$gl_data['base_net_value']	= $base_net_value;
						$gl_data['twin_net_value']	= $line->twin_net_value - $gl_data['twin_net_value'];
					}
					
 					if ($poreceivedline->isAccrued())
 					{
 						// The GRN has been accrued so need to post to the accruals control
	 					$gl_data['glaccount_id']	= $accruals_control_account;
						$gl_data['glcentre_id']		= $balance_sheet_cost_centre;
 					}
 					
				}
				
			}
			
			// Calculate tax value if EU acquisition
			if (($eu_acquisition || $vat_postponed || $reverse_charge) && ($line->tax_rate_id))
			{
				if (isset($tax_rates[$line->tax_rate_id]) && ($tax_rates[$line->tax_rate_id] > 0))
				{
					$tax_rate_mult = (1 + ($tax_rates[$line->tax_rate_id] / 100));
					$eu_gl_data['value'] += ($line->base_net_value * $tax_rate_mult) - $line->base_net_value;
					$eu_gl_data['twinvalue'] += ($line->twin_net_value * $tax_rate_mult) - $line->twin_net_value;
				}
			}
			
			// Make the GL Transaction for the invoice line
			$element = GLTransaction::makeCBLine($gl_data, $newerrors);
			
			if($element!==FALSE)
			{
				$gl_transactions[]=$element;
			}
			else
			{
				$errors+=$newerrors;
				return FALSE;
			}
		}
		
		if ($eu_acquisition)
		{
			
			$eu_tax_elements = GLTransaction::makeEuTax($eu_gl_data, $newerrors);
			
			foreach ($eu_tax_elements as $eu_tax_element)
			{
				if ($eu_tax_element === false)
				{
					$errors+=$newerrors;
					return false;
				}
				$gl_transactions[] = $eu_tax_element;
			}
		}

		if ($vat_postponed)
		{
			
			$eu_tax_elements = GLTransaction::makeTax($eu_gl_data, 'PVA', $newerrors);
			
			foreach ($eu_tax_elements as $eu_tax_element)
			{
				if ($eu_tax_element === false)
				{
					$errors+=$newerrors;
					return false;
				}
				$gl_transactions[] = $eu_tax_element;
			}
		}

		if ($reverse_charge)
		{
			
			$eu_tax_elements = GLTransaction::makeTax($eu_gl_data, 'RC', $newerrors);
			
			foreach ($eu_tax_elements as $eu_tax_element)
			{
				if ($eu_tax_element === false)
				{
					$errors+=$newerrors;
					return false;
				}
				$gl_transactions[] = $eu_tax_element;
			}
		}
		
		if (count($errors)>0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
}

// end of PInvoice.php
