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
			'vat_postponed'		=> $glparams->vat_postponed_account(),
			'vat_reverse_charge'=> $glparams->vat_reverse_charge_account(),
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

			$values = $this->getVATvalues($year, $tax_period);
			
			$output_tax = $values['outputs']; //$this->getVATSum(1)
			
			$input_tax = $values['inputs']; //$this->getVATSum(4)
			
			$total_tax = bcsub($input_tax, $output_tax);
			
			$input_tax = bcmul($input_tax,-1);
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

		try
		{
			$return = new VatReturn;
			$return->updateVatReturnBoxes($year, $tax_period, $values);
		}
		catch (VatReturnStorageException $e)
		{
			$errors[] = $e->getMessage();
		}

		if (count($errors) > 0)
		{
			$db->FailTrans();
		}
		
		return $db->CompleteTrans();
	}
	
	/**
	 * Get VAT 'Box' values
	 * 
	 * Note:
	 *   Box3 = Box1 + Box2
	 *   Box5 = Box3 + Box4
	 *   'outputs' and 'inputs' are used for posting when closing the vat period: @see Vat::closePeriod()
	 * 
	 * @param int $year
	 * @param int $tax_period
	 * 
	 * @return array ['Box[n]' => 0.00, ...]
	 */
	function getVATvalues($year=null, $tax_period=null, $return_id=null)
	{
		$qparams = [$year, $tax_period];
		$query = <<<'QUERY'
select tax_period,
coalesce(sum((select sum(vat) from gltransactions_vat_outputs vo where vo.glperiods_id=glp.id)) + coalesce(sum((select sum(vat) from gl_taxpvpurchases vo where vo.glperiods_id=glp.id)),0.00)+ coalesce(sum((select sum(vat) from gl_taxrcpurchases vo where vo.glperiods_id=glp.id)),0.00), 0.00) as "Box1",
coalesce(sum((select sum(vat) from gl_taxeupurchases vo where vo.glperiods_id=glp.id)), 0.00) as "Box2",
coalesce(sum((select sum(vat) from gltransactions_vat_inputs vo where vo.glperiods_id=glp.id)) + coalesce(sum((select sum(vat) from gl_taxeupurchases vo where vo.glperiods_id=glp.id)),0.00)+ coalesce(sum((select sum(vat) from gl_taxpvpurchases vo where vo.glperiods_id=glp.id)),0.00)+ coalesce(sum((select sum(vat) from gl_taxrcpurchases vo where vo.glperiods_id=glp.id)),0.00), 0.00) as "Box4",
coalesce(sum((select sum(net) from gltransactions_vat_outputs vo where vo.glperiods_id=glp.id))+coalesce(sum((select sum(net) from gl_taxrcpurchases vo where vo.glperiods_id=glp.id)), 0.00), 0.00) as "Box6",
coalesce(sum((select sum(net) from gltransactions_vat_inputs vo where vo.glperiods_id=glp.id)), 0.00) as "Box7",
coalesce(sum((select sum(net) from gltransactions_vat_outputs vo where vo.glperiods_id=glp.id and eutaxstatus='T')), 0.00) as "Box8",
coalesce(sum((select sum(net) from gltransactions_vat_inputs vo where vo.glperiods_id=glp.id and eutaxstatus='T')), 0.00) as "Box9",
coalesce(sum((select sum(vat) from gltransactions_vat_outputs where glperiods_id=glp.id)), 0.00) as "outputs",
coalesce(sum((select sum(vat) from gltransactions_vat_inputs vo where vo.glperiods_id=glp.id)), 0.00) as "inputs"
from gl_periods glp
where year=? and tax_period=?
group by tax_period
QUERY;

		

		$db = DB::Instance();
		$boxr = $db->getAll($query, $qparams);
		$boxr = $boxr[0];

		// Added by MJS July 2021 to take account of post GL close 'manual' adjustments to VAT return
		$qparams2 = $return_id;
		$query2 = <<<'QUERY'
select coalesce(sum(vat_due_sales),0.00) as "Box1_adj", coalesce(sum(vat_reclaimed_curr_period), 0.00) as "Box4_adj", coalesce(sum(total_value_sales_ex_vat),0.00) as "Box6_adj",
coalesce(sum(total_value_purchase_ex_vat),0.00) as "Box7 adj"
from vat_adjustment
where vat_return_id=?
group by vat_return_id
QUERY;
		$db2 = DB::Instance();
		$boxadj = $db2->getAll($query2, $qparams2);
		$boxadj = $boxadj[0];

		if (!is_null($boxadj))
		{
			$boxr['Box1'] = $boxr['Box1'] + $boxadj['Box1_adj'];
			$boxr['Box4'] = $boxr['Box4'] + $boxadj['Box4_adj'];
			$boxr['Box6'] = $boxr['Box6'] + $boxadj['Box6_adj'];
			$boxr['Box7'] = $boxr['Box7'] + $boxadj['Box7_adj'];

		}

		$boxr['Box3'] = $boxr['Box1'] + $boxr['Box2'];
		$boxr['Box5'] = $boxr['Box3'] - $boxr['Box4'];
		return $boxr;
	}

	/**
	 * Format and calculate VAT values for display
	 * 
	 * @param int $year
	 * @param int $tax_period
	 * 
	 * @return array
	 */
	function getVatBoxes($year, $tax_period){

		$values = $this->getVATvalues($year, $tax_period);

		foreach ($this->titles as $key=>$value)
		{
			$boxes[$key]['box_num']	= 'Box '.$key.' : ';
			$boxes[$key]['value']	= 0;
		}

		$boxes[100]['box_num'] = '100';
		$boxes[100]['value'] = 0;

		// VAT due on sales (box 1)
		$value = $values['Box1'];
		$boxes[1]['value'] = empty($value)?0:$value;

		// VAT due on EU purchases (box 2)
		$value = $values['Box2'];
		$boxes[2]['value'] = empty($value)?0:$value;

		// Output tax (box 3)
		$boxes[3]['value'] = $boxes[1]['value'] + $boxes[2]['value'];
		
		// Input tax (box 4)
		$value = $values['Box4'];
		$boxes[4]['value'] = empty($value)?0:$value;
		
		// Net tax (box 5)
		$boxes[5]['value'] = $boxes[3]['value'] - $boxes[4]['value'];
		
		// Sales excluding VAT (box 6)
		$value = $values['Box6'];
		$boxes[6]['value'] = empty($value)?0:$value;
		
		// Purchases excluding VAT (box 7)
		$value = $values['Box7'];
		$boxes[7]['value'] = empty($value)?0:$value;
		
		// EU sales excluding VAT (box 8)
		$value = $values['Box8'];
		$boxes[8]['value'] = empty($value)?0:$value;
		
		// EU purchases excluding VAT (box 9)
		$value = $values['Box9'];
		$boxes[9]['value'] = empty($value)?0:$value;

		//inputs
		$value = $values['inputs'];
		$boxes[100]['value'] = empty($value)?0:$value;
		
		foreach ($boxes as $key=>$value)
		{
			$boxes[$key]['value'] = sprintf('%.2f',$boxes[$key]['value']);
		}
		
		return $boxes;
	}
}

// End of Vat
