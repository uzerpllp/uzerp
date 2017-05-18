<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpenseLine extends DataObject
{
	
	protected $version = '$Revision: 1.10 $';
	
	public function __construct($tablename = 'expenses_lines')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
			
		// Set specific characteristics
		
		// Define relationships
		$this->hasOne('Expense', 'expenses_header_id', 'expense_header');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
 		$this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate'); 
		
		// Define field formats
 		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));
		
		// Define field defaults
		$this->getField('net_value')->setDefault('0.00');	
		$this->getField('tax_value')->setDefault('0.00');	
		$this->getField('gross_value')->setDefault('0.00');	
		
		// Define validation
		$this->validateUniquenessOf(array('expenses_header_id', 'line_number'));
		
		// Define enumerated types
	
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
	
	static function Factory($data, &$errors, $do)
	{

		if (!isset($data['expenses_header_id']))
		{
			$errors[]='Cannot find expenses header';
		}
		else
		{
			$header = DataObjectFactory::Factory('Expense');
			$header->load($data['expenses_header_id']);
		}
		
		if ($header)
		{
			$data['currency_id']		= $header->currency_id;
			$data['rate']				= $header->rate;
			$data['twin_currency_id']	= $header->twin_currency_id;
			$data['twin_rate']			= $header->twin_rate;
			
			if (empty($data['line_number']) && empty($data['id']))
			{
				$data['line_number']	= $header->lines->count()+1;
			}
		}
		
		if (isset($data['tax_rate_id']))
		{
			$taxrate = DataObjectFactory::Factory('TaxRate');
			
			$taxrate->load($data['tax_rate_id']);
			
			if ($taxrate)
			{
				$tax_percentage=$taxrate->percentage;
			}
		}

		if ($tax_percentage==0 && $data['tax_value']!=0)
		{
			$errors[]='Tax value should be zero for this tax rate';
		}
		elseif ($tax_percentage!=0 && $data['tax_value']==0)
		{
			$errors[]='Tax value required for this tax rate';
		}

		if ($data['rate']==1)
		{
			$data['base_net_value'] = $data['net_value'];
			$data['base_tax_value'] = $data['tax_value'];
			$data['base_gross_value'] = $data['gross_value'];
		}
		else
		{
			$data['base_net_value'] = round(bcdiv($data['net_value'],$data['rate'],4),2);
			$data['base_tax_value'] = round(bcdiv($data['tax_value'],$data['rate'],4),2);
			$data['base_gross_value'] = round(bcadd($data['base_tax_value'],$data['base_net_value']),2);
		}
		
		//and to the twin-currency
		$data['twin_net_value'] = round(bcmul($data['base_net_value'],$data['twin_rate'],4),2);
		$data['twin_tax_value'] = round(bcmul($data['base_tax_value'],$data['twin_rate'],4),2);
		$data['twin_gross_value'] = round(bcadd($data['twin_tax_value'],$data['twin_net_value']),2);

		$line = parent::Factory($data, $errors, $do);
		
		if (count($errors)>0 || !$line)
		{
			return false;
		}
		
		return $line;
	}
	

	public function delete () {

		$flash = Flash::Instance();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$result = parent::delete();
		
		// Save the header to update the header totals
		if ($result && !$this->expense_header->save())
		{
			$result = false;
			$flash->addError('Error updating header');
		}
		
		if ($result)
		{
			// Now update the line numbers of following lines
			$expenselines = new ExpenseLineCollection($this);
			
			$sh = new SearchHandler($expenselines, false);
			
			$sh->addConstraint(new Constraint('expenses_header_id', '=', $this->expenses_header_id));
			$sh->addConstraint(new Constraint('line_number', '>', $this->line_number));
			
			if ($expenselines->update('line_number', '(line_number-1)', $sh) === false)
			{
				$flash->addError('Error updating line numbers '.$db->ErrorMsg());
				$result=false;
			}
		}
		
		if ($result === false) {
			$db->FailTrans();
		}			
		
		$db->CompleteTrans();
		return $result;
		
	}
	
	public function save($debug = false)
	{
			
		$result = false;
		
		$db = DB::Instance();
		$db->startTrans();
		
		if (parent::save($debug))
		{
		
			$expense = DataObjectFactory::Factory('Expense');
			
			$expense->load($this->expenses_header_id);
			
			if ($expense->isLoaded())
			{
				// save the header to force update of header totals
				$result=$expense->save($debug);
			}
			
		}

		if ($result === false) {
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
}

// End of ExpenseLine