<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Vat extends GLTransaction
{

	protected $version = '$Revision: 1.14 $';
	
	public $glperiod_ids = array();
	
	public $tax_period_closed;
	
	public $gl_period_closed;
	
	private $control_accounts;
	
	public $currencySymbol = '';
	
	public $titles = array();
	
	function __construct() {
		parent::__construct();
		$this->titles = array(1=>'VAT Due On Sales'
							 ,2=>'VAT Due On EU Purchases'
							 ,3=>'Output Tax'
							 ,4=>'Input Tax'
							 ,5=>'Net Tax'
							 ,6=>'Sales Exc. VAT'
							 ,7=>'Purchases Exc. VAT'
							 ,8=>'EU Sales Exc. VAT'
							 ,9=>'EU Purchases Exc. VAT');
		
	}
	
	function vatreturn($tax_period = '', $year='', &$errors = array())
	{

		$errors = array();
		
		$this->getCurrencySymbol($errors);
		
		$this->glperiod_ids = GLPeriod::getIdsForTaxPeriod($tax_period, $year);
		
		$this->getControlAccounts($errors);
		
		$this->getTaxPeriodStatus($tax_period, $year, $errors);

	}
	
	private function getCurrencySymbol(&$errors = array())
	{
		
		$glparams = DataObjectFactory::Factory('GLParams');
		
		$currency_id = $glparams->base_currency();
		
		if ($currency_id !== false) {
			
			$currency = DataObjectFactory::Factory('Currency');
			
			if ($currency->load($currency_id))
			{
				$this->currencySymbol = $currency->symbol;
			}
		}
		
		if (empty($this->currencySymbol))
		{
			$errors[]='No currency symbol defined';
		}

	}
	
	private function getTaxPeriodStatus ($tax_period, $year, &$errors = array())
	{
		$this->tax_period_closed = false;
		
		$this->gl_period_closed = false;
		
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$glperiod->getTaxPeriodEnd($tax_period, $year);
		
		if ($glperiod)
		{
			$this->tax_period_closed = $glperiod->tax_period_closed;
			$this->gl_period_closed  = $glperiod->closed;
		}
		else
		{
			$errors[] = 'Failed to get period status';
		}
	}
	
	private function getControlAccounts (&$errors=array())
	{
		$glparams = DataObjectFactory::Factory('GLParams');
		
		$this->control_accounts = array(
			'vat_input'			=> $glparams->vat_input(),
			'vat_output'		=> $glparams->vat_output(),
			'sales_ledger'		=> $glparams->sales_ledger_control_account(),
			'purchase_ledger'	=> $glparams->purchase_ledger_control_account(),
			'retained_profits'	=> $glparams->retained_profits_account(),
			'vat_control'		=> $glparams->vat_control_account(),
			'eu_acquisitions'	=> $glparams->eu_acquisitions(),
		);
		
		if (in_array(false, $this->control_accounts, true)) {
			$errors[]='Not all control accounts have been assigned.';
		}
		
	}

	function getTransactions($box, $paging = false)
	{
		if (in_array(false, $this->control_accounts, true))
		{
			return false;
		}
		
		$gltransactions = new GLTransactionCollection($this);
		
		$gltransactions->getVAT($box, $this->glperiod_ids, $this->control_accounts, false, $paging);
		
		$map_value_field = 'value';
		
		foreach ($gltransactions as $gltransaction)
		{
			$gltransaction->setAdditional('company');
			$gltransaction->company = $gltransaction->company();
			
			$gltransaction->setAdditional('ext_reference');
			$gltransaction->ext_reference = $gltransaction->ext_reference();
		}
		
		return $gltransactions;
	}

	function closePeriod($tax_period, $year, &$errors)
	
	{
		$db=DB::Instance();
		
		$db->StartTrans();
		
		foreach ($this->glperiod_ids as $glperiod_id)
		{
			$glperiod = DataObjectFactory::Factory('GLPeriod');
			
			$glperiod->load($glperiod_id);
			
			if ($glperiod->isLoaded())
			{
				$glperiod->tax_period_closed = true;
				
				if (!$glperiod->save())
				{
					$errors[] = 'Error trying to close tax period';
					break;
				}
			}
			else
			{
				$errors[] = 'Error trying to close tax period';
				break;
			}
		}
		
		if (count($errors)==0)
		{
		
			$this->tax_period_closed = true;
			
			$output_tax = $this->getVATSum(1);
			
			$input_tax = $this->getVATSum(4);
			
			$total_tax = bcsub($input_tax, $output_tax);
			
			$input_tax = bcmul($input_tax,-1);
//			$total_tax=$this->getVATSum(5);
//			if ($total_tax!=($output_tax-$input_tax)) {
//				$errors[]='Total VAT does not equal Output Tax minus Input Tax';
//			}
		}
		
		if (count($errors)==0)
		{
			$net_tax_element = array();
			
			$glparams = DataObjectFactory::Factory('GLParams');
			
			$net_tax_element['glcentre_id'] = $glparams->balance_sheet_cost_centre();
			
			$glperiod = GLPeriod::getPeriod(date('Y-m-d'));
			
			if ((!$glperiod) || (count($glperiod) == 0))
			{
				$errors[] = 'No period exists for this date';
			}
			else
			{
				$net_tax_element['glperiods_id']	 = $glperiod['id'];
				$net_tax_element['docref']			 = $year.'-'.$tax_period;
				$net_tax_element['transaction_date'] = date(DATE_FORMAT);
				$net_tax_element['source']			 = 'V'; // V = VAT Return
				$net_tax_element['type']			 = 'N'; // N = Net Tax, P = Payment
				$net_tax_element['comment']			 = 'VAT Return: '.$year.' - Tax Period '.$tax_period;
				$net_tax_element['value']			 = $input_tax;
				$net_tax_element['glaccount_id']	 = $this->control_accounts['vat_input'];
				
				$this->setTwinCurrency($net_tax_element);
				
				$gltransactions[] = GLTransaction::Factory($net_tax_element, $errors, 'GLTransaction');
				
				$net_tax_element['value']			= $output_tax;
				$net_tax_element['glaccount_id']	= $this->control_accounts['vat_output'];
				
				$this->setTwinCurrency($net_tax_element);
				
				$gltransactions[] = GLTransaction::Factory($net_tax_element, $errors, 'GLTransaction');
				
				$net_tax_element['value']			= $total_tax;
				$net_tax_element['glaccount_id']	= $this->control_accounts['vat_control'];
				
				$this->setTwinCurrency($net_tax_element);
				
				$gltransactions[] = GLTransaction::Factory($net_tax_element, $errors, 'GLTransaction');
				
				$this->saveTransactions($gltransactions, $errors);
			}
		}
		
		if (count($errors) > 0)
		{
			$db->FailTrans();
		}
		
		return $db->CompleteTrans();
	}
	
	function getVatBoxes(){

		foreach ($this->titles as $key=>$value)
		{
			$boxes[$key]['box_num']	= 'Box '.$key.' : ';
			$boxes[$key]['value']	= 0;
		}

		$glperiods = implode(', ', $this->glperiod_ids);
		
		if (in_array(false, $this->control_accounts, true))
		{
			return false;
		}
		$control_account_ids = implode(', ', $this->control_accounts);
		$db = DB::Instance();
		// VAT due on sales (box 1)
		$value = $this->getVATSum('1');
		$boxes[1]['value'] = empty($value)?0:$value;

		// VAT due on EU purchases (box 2)
		$value = $this->getVATSum('2');
		$boxes[2]['value'] = empty($value)?0:$value;

		// Output tax (box 3)
		$boxes[3]['value'] = $boxes[1]['value'] + $boxes[2]['value'];
		
		// Input tax (box 4)
		$value = $this->getVATSum('4');
		$boxes[4]['value'] = empty($value)?0:$value + $boxes[2]['value'];
		
		// Net tax (box 5)
		$boxes[5]['value'] = $boxes[3]['value'] - $boxes[4]['value'];
		
		// Sales excluding VAT (box 6)
		$value = $this->getVATSum('6');
		$boxes[6]['value'] = empty($value)?0:$value;
		
		// Purchases excluding VAT (box 7)
		$value = $this->getVATSum('7');
		$boxes[7]['value'] = empty($value)?0:$value;
		
		// EU sales excluding VAT (box 8)
		$value = $this->getVATSum('8');
		$boxes[8]['value'] = empty($value)?0:$value;
		
		// EU purchases excluding VAT (box 9)
		$value = $this->getVATSum('9');
		$boxes[9]['value'] = empty($value)?0:$value;
		
		foreach ($boxes as $key=>$value)
		{
			$boxes[$key]['value'] = sprintf('%.2f',$boxes[$key]['value']);
		}
		
		return $boxes;
	}
	
	protected function getVATSum ($box)
	{
		$gltransactions = new GLTransactionCollection($this);
		
		$gltransactions->getVAT(array('box'=>$box), $this->glperiod_ids, $this->control_accounts, true);
		
		if ($gltransactions->count()==1)
		{
			return $gltransactions->getContents(0)->sum;
		}
		else
		{
			return 0;
		}
	}
	
}

// End of Vat
