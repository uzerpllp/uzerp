<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SInvoiceCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.12 $';
	
	public $field;
	protected $view;
	protected $customerorders;
		
	function __construct($do = 'SInvoice', $tablename = 'si_headeroverview')
	{
		parent::__construct($do, $tablename);

		$this->view = '';
	}
	
	function getOverdueInvoices()
	{
		$sh = new SearchHandler($this, false);
		
		$sh->addConstraint(new Constraint('due_date', '<=', fix_date(date(DATE_FORMAT))));
		$sh->addConstraint(new Constraint('status', '=', 'O'));
		
		$this->load($sh);
	}

	function getSalesHistory()
	{
		$cache = Cache::Instance();
        $customersales = $cache->get('saleshistory');
        if (!empty($customersales)) {
			return $customersales;
        }

		$db = DB::Instance();
		
		$startmonth		= fix_date('01/'.date('m/Y'));
		$currentmonth	= date('Y/m');
		$startdate		= fix_date(date(DATE_FORMAT, strtotime("-12 months", strtotime((string) $startmonth))));
		$today			= fix_date(date(DATE_FORMAT));
		$enddate		= $today;
		$lastweekend	= fix_date(date(DATE_FORMAT, strtotime("last sunday")));
		$lastweekstart	= fix_date(date(DATE_FORMAT, strtotime("-6 days", strtotime((string) $lastweekend))));
		$thisweekend	= fix_date(date(DATE_FORMAT, strtotime("next sunday")));
		$thisweekstart	= fix_date(date(DATE_FORMAT, strtotime("-6 days", strtotime((string) $thisweekend))));
		
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$currentperiod = $glperiod->loadPeriod(date(DATE_FORMAT));
		
		$glperiod->loadBy(array('year', 'period'), array($currentperiod->year, 1));
		
		$yearstart = fix_date($glperiod->getPeriodStartDate($currentperiod->id));
		
		for ($i=11; $i>=0; $i--)
		{
			$date=date('Y/m',strtotime("+".$i." months",strtotime((string) $startdate)));
			$this->customerorders['previous'][$date]['start_date']	= fix_date(date(DATE_FORMAT,strtotime("+".$i." months",strtotime((string) $startdate))));
			$this->customerorders['previous'][$date]['end_date']	= fix_date(date(DATE_FORMAT,strtotime("+".($i+1)." months",strtotime((string) $startdate))-1));;
			$this->customerorders['previous'][$date]['value']		= '0.00';
		}

		
		$this->customerorders['current']['today']['start_date']	= $today;
		$this->customerorders['current']['today']['end_date']	= $today;
		$this->customerorders['current']['today']['value']		= '0.00';
		
		$this->customerorders['current']['this_week']['start_date']	= $thisweekstart;
		$this->customerorders['current']['this_week']['end_date']	= $thisweekend;
		$this->customerorders['current']['this_week']['value']		= '0.00';
		
		$this->customerorders['current']['last_week']['start_date']	= $lastweekstart;
		$this->customerorders['current']['last_week']['end_date']	= $lastweekend;
		$this->customerorders['current']['last_week']['value']		= '0.00';
		
		$this->customerorders['current']['this_month_to_date']['start_date'] = $startmonth;
		$this->customerorders['current']['this_month_to_date']['end_date']	 = $today;
		$this->customerorders['current']['this_month_to_date']['value']		 = '0.00';

		$this->customerorders['current']['year_to_date']['start_date']	= $yearstart;
		$this->customerorders['current']['year_to_date']['end_date']	= $today;
		$this->customerorders['current']['year_to_date']['value']		= '0.00';
		
		$current = DataObjectFactory::Factory('SInvoice');
		
		$transaction_types = $current->getEnumOptions('transaction_type');
		
		$multipliers = $current->getMultipliers();
		
		foreach ($transaction_types as $transaction_type=>$transaction_type_title)
		{
			if (!array_key_exists($transaction_type, $multipliers)) {
				// avoid invoice templates
				break;
			}
			$sh = new SearchHandler($this, false);
			
			$sh->setFields(array("to_char(invoice_date, 'YYYY/MM')",'sum(base_net_value) as value'));
			
			$sh->setGroupBy("to_char(invoice_date, 'YYYY/MM')");
			
			$sh->setOrderBy("to_char(invoice_date, 'YYYY/MM')");
			
			$sh->addConstraint(new Constraint('invoice_date', 'between', "'".$startdate."' and '".$enddate."'"));
			$sh->addConstraint(new Constraint('transaction_type', '=', $transaction_type));
			
			$this->load($sh);

			foreach ($this as $invoice)
			{
				$value=bcmul($invoice->value, (string) $multipliers[$transaction_type]);
				
				if ($invoice->id==$currentmonth)
				{
					$this->customerorders['current']['this_month_to_date']['value'] = bcadd($value, (string) $this->customerorders['current']['this_month_to_date']['value']);
				}
				else
				{
					$this->customerorders['previous'][$invoice->id]['value'] = bcadd($value, (string) $this->customerorders['previous'][$invoice->id]['value']);
				}
			}
			$this->clear();
		}

		foreach ($transaction_types as $transaction_type=>$transaction_type_title)
		{
			if (!array_key_exists($transaction_type, $multipliers)) {
				// avoid invoice templates
				break;
			}
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('invoice_date', 'between', "'".$lastweekstart."' and '".$lastweekend."'"));
			$cc->add(new Constraint('transaction_type', '=', $transaction_type));
			
			$value = bcmul((string) $current->getSum('base_net_value', $cc), (string) $multipliers[$transaction_type]);
			
			$this->customerorders['current']['last_week']['value'] = bcadd($value,(string) $this->customerorders['current']['last_week']['value']);
		}
		
		foreach ($transaction_types as $transaction_type=>$transaction_type_title)
		{
			if (!array_key_exists($transaction_type, $multipliers)) {
				// avoid invoice templates
				break;
			}
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('invoice_date', 'between', "'".$thisweekstart."' and '".$thisweekend."'"));
			$cc->add(new Constraint('transaction_type', '=', $transaction_type));
			
			$value = bcmul((string) $current->getSum('base_net_value', $cc), (string) $multipliers[$transaction_type]);
			
			$this->customerorders['current']['this_week']['value'] = bcadd($value,(string) $this->customerorders['current']['this_week']['value']);
		}		

		foreach ($transaction_types as $transaction_type=>$transaction_type_title)
		{
			if (!array_key_exists($transaction_type, $multipliers)) {
				// avoid invoice templates
				break;
			}
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('invoice_date', '=', $db->qstr(fix_date(date(DATE_FORMAT)))));
			$cc->add(new Constraint('transaction_type', '=', $transaction_type));
			
			$value = bcmul((string) $current->getSum('base_net_value', $cc), (string) $multipliers[$transaction_type]);
			
			$this->customerorders['current']['today']['value'] = bcadd($value,(string) $this->customerorders['current']['today']['value']);
		}		
		
		foreach ($transaction_types as $transaction_type=>$transaction_type_title)
		{
			if (!array_key_exists($transaction_type, $multipliers)) {
				// avoid invoice templates
				break;
			}
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('invoice_date', 'between', "'".$yearstart."' and '".$thisweekend."'"));
			$cc->add(new Constraint('transaction_type', '=', $transaction_type));
			
			$value = bcmul((string) $current->getSum('base_net_value', $cc), (string) $multipliers[$transaction_type]);
			
			$this->customerorders['current']['year_to_date']['value'] = bcadd($value,(string) $this->customerorders['current']['year_to_date']['value']);
		}		
		

		$cache->add('saleshistory', $this->customerorders, 3600);
		return $this->customerorders;
	}
	
}

// End of SInvoiceCollection
