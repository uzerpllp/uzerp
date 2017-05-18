<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class SInvoiceLineCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.9 $';
	
	public $field;
		
	protected $customerorders=array();
		
	function __construct($do='SInvoiceLine', $tablename='si_linesoverview') {
		parent::__construct($do, $tablename);
		$this->orderby='line_number';
	}

	function getTopSales($top=10, $type='customer') {
		$startdate=fix_date('01/'.date('m/Y'));
		$enddate=fix_date(date(DATE_FORMAT,strtotime("-1 days",strtotime("+1 months",strtotime($startdate)))));
		
		$fields=array();
		switch ($type) {
			case ('customer'):
				$fields[]='customer';
				$sumby='base_net_value';
				break;
			case ('item by qty'):
				$fields[]='stitem';
				$sumby='sales_qty';
				$sh->addConstraint(new Constraint('stitem', 'is not', 'NULL'));
				break;
			case ('item by value'):
				$fields[]='stitem';
				$sumby='base_net_value';
				$sh->addConstraint(new Constraint('stitem', 'is not', 'NULL'));
				break;
		}
		
		$invoice_fields = $fields;
		$invoices = clone $this;
		$sh = new SearchHandler($invoices, false);
		$sh->setGroupBy($invoice_fields);
		$sh->setOrderby($invoice_fields);
		$invoice_fields[]='sum('.$sumby.') as value';
		$sh->setFields($invoice_fields);
		$sh->addConstraint(new Constraint('invoice_date', 'between', "'".$startdate."' and '".$enddate."'"));
		$sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
		$invoice_data = $invoices->load($sh, null, RETURN_ROWS);
		
		$credit_fields = $fields;
		$sh = new SearchHandler($this, false);
		$sh->setGroupBy($credit_fields);
		$sh->setOrderby($credit_fields);
		$credit_fields[]='sum('.$sumby.') as value';
		$sh->setFields($credit_fields);
		$sh->addConstraint(new Constraint('invoice_date', 'between', "'".$startdate."' and '".$enddate."'"));
		$sh->addConstraint(new Constraint('transaction_type', '=', 'C'));
		$credits = $this->load($sh, null, RETURN_ROWS);
		
		$data = array();

		if (is_array($invoice_data))
		{
			foreach ($invoice_data as $invoice)
			{
				$data[$invoice['id']] = $invoice['value'];
			}
		}
		
		if (is_array($credits))
		{
			foreach ($credits as $credit)
			{
				if (isset($data[$credit['id']]))
				{
					$data[$credit['id']] -= $credit['value'];
				}
			}
		}
		
		arsort($data, SORT_NUMERIC);
		
		$typesarray=array('customer'=>'By Customer'
						 ,'item by qty'=>'By Item Quantity'
						 ,'item by value'=>'By Item Value');
		$this->customerorders=array('source'=>'invoices'
								   ,'controller'=>'sinvoices'
								   ,'submodule'=>'sales_invoicing'
								   ,'types'=>$typesarray
								   ,'type'=>$type
								   ,'details'=>array());

		
		$count=0;
		foreach ($data as $key=>$value) {
			if ($count<$top) {
				$this->customerorders['details'][$key]=$value;
			} else {
				break;
			}
			$count++;
		}

		return $this->customerorders;
	}

}
?>