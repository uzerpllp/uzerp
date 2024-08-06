<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CustomerServiceCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.19 $';
	
	public $field;
		
	protected $customerorders=array();
		
	function __construct($do = 'SInvoiceLine', $tablename = 'customer_service')
	{
		
		parent::__construct($do, $tablename);
		
		$this->orderby = array(
			'product_group',
			'customer',
			'stitem',
			'due_despatch_date'
		);
		
		$this->direction = array('ASC', 'ASC', 'ASC', 'DESC');
		
	}

	function setSearch ($search = array(), $use_session = FALSE)
	{
		
		$enddate = fix_date('01/' . date('m/Y'));
		
		if (isset($search['start']) && !empty($search['start']))
		{
			$startdate = fix_date(date(DATE_FORMAT, strtotime($search['start'] . '/01')));
		}
		else
		{
			$startdate = fix_date(date(DATE_FORMAT, strtotime("-12 months", strtotime((string) $enddate))));
		}
		
		if (isset($search['end']) && !empty($search['end']))
		{
			$enddate = fix_date(date(DATE_FORMAT, strtotime("-1 day", strtotime("+1 month", strtotime($search['end'] . '/01')))));
		}
		else
		{
			$enddate = fix_date(date(DATE_FORMAT));
		}
				
		$sh = new SearchHandler($this, $use_session);
		$sh->addConstraint(new Constraint('despatch_date', 'between', "'" . $startdate . "' and '" . $enddate . "'"));
		
		// this is such a repetative job, just loop through this fields array
		$fields = array('id', 'product_group','slmaster_id', 'cs_failurecode_id');
		
		foreach ($fields as $field)
		{
			
			if (isset($search[$field]) && !empty($search[$field]))
			{
				$sh->addConstraint(new Constraint($field, '=', $search[$field]));
			}
		
		}
		
		return $sh;
		
	}

	function productGroupList ()
	{
		
		$prodgroups						= array();
		$stprodgroups					= DataObjectFactory::Factory('STProductgroup');
		$stprodgroups->identifierField	= 'product_group';
		$groups							= $stprodgroups->getAll();
		
		foreach ($groups as $prodgroup)
		{
			$prodgroups[$prodgroup] = $prodgroup;
		}
		
		ksort($prodgroups);
		
		return $prodgroups;
		
	}
	
	function customerList()
	{
		
		$customers = DataObjectFactory::Factory('SLCustomer');
		return $customers->getAll(null, FALSE, TRUE);
		
	}
	
	function periodList ()
	{
		
		$periods	= array();
		$enddate	= fix_date(date(DATE_FORMAT));
		$startdate	= fix_date('01/' . date('m/Y'));
		$startdate	= fix_date(date(DATE_FORMAT, strtotime("-12 months", strtotime((string) $startdate))));
		
		while ($startdate < $enddate)
		{
			$period				= date('Y/m', strtotime((string) $startdate));
			$periods[$period]	= $period;
			$startdate			= fix_date(date(DATE_FORMAT,strtotime("+1 months", strtotime((string) $startdate))));
		}
		
		return $periods;
		
	}
	
	function getServiceHistory()
	{
		
		$enddate	= fix_date('01/' . date('m/Y'));
		$startdate	= fix_date(date(DATE_FORMAT, strtotime("-12 months", strtotime((string) $enddate))));
		$enddate	= fix_date(date(DATE_FORMAT));
		
		$this->_tablename='customer_service_summary';
		$sh = new SearchHandler($this, FALSE);
		$sh->addConstraint(new Constraint('despatch_date', 'between', "'" . $startdate . "' and '" . $enddate . "'"));
		$sh->setFields(
			array(
				'year_month',
				'sum(ontime) as ontime',
				'sum(infull) as infull',
				'sum(otif) as otif',
				'sum(count) as count'
			)
		);
		
		$sh->setGroupBy("year_month");
		$sh->setOrderBy("year_month");
		
		$this->load($sh);

		for ($i = 0; $i < 13; $i++)
		{
			$date = date('Y/m', strtotime("+" . $i . " months", strtotime((string) $startdate)));
			$this->customerorders['previous'][$date]['ontime']			= 0;
			$this->customerorders['previous'][$date]['infull']			= 0;
			$this->customerorders['previous'][$date]['ontime_infull']	= 0;
			$this->customerorders['previous'][$date]['count']			= 0;
		}
		
		foreach ($this as $despatch)
		{
			
			if (isset($this->customerorders['previous'][$despatch->id]))
			{
				$this->customerorders['previous'][$despatch->id]['ontime']			= $despatch->ontime;
				$this->customerorders['previous'][$despatch->id]['infull']			= $despatch->infull;
				$this->customerorders['previous'][$despatch->id]['ontime_infull']	= $despatch->otif;
				$this->customerorders['previous'][$despatch->id]['count']			= $despatch->count;
			}
			
		}
		
		foreach ($this->customerorders['previous'] as $date=>$month)
		{
			
			if ($month['count']==0)
			{
				$this->customerorders['previous'][$date]['ontime%']			= 0;
				$this->customerorders['previous'][$date]['infull%']			= 0;
				$this->customerorders['previous'][$date]['ontime_infull%']	= 0;
			}
			else
			{
				$this->customerorders['previous'][$date]['ontime%']			= $month['ontime'] * 100 / $month['count'];
				$this->customerorders['previous'][$date]['infull%']			= $month['infull'] * 100 / $month['count'];
				$this->customerorders['previous'][$date]['ontime_infull%']	= ($month['ontime_infull']) * 100 / $month['count'];
			}
			
		}
		
		return $this->customerorders;
		
	}

	function getServiceSummary($sh, $level='customer')
	{

		$this->_tablename='customer_service_summary';
		
		$sh->setFields(
			array(
				'product_group||customer',
				'product_group',
				'customer',
				'slmaster_id',
				'sum(ontime) as ontime',
				'sum(infull) as infull',
				'sum(otif) as otif',
				'sum(count) as count'
			)
		);
		
		$sh->setGroupBy(array('product_group', 'customer', 'slmaster_id'));
		$sh->setOrderBy(array('product_group', 'customer', 'slmaster_id'));
		
		$this->load($sh);
		$currentproduct = '';
		
		$previous_group			= '';
		$total_ontime_c			= $group_ontime_c			= 0;
		$total_infull_c			= $group_infull_c			= 0;
		$total_ontime_infull_c	= $group_ontime_infull_c	= 0;
		$total_count			= $group_count				= 0;
		foreach ($this as $despatch)
		{
			if (empty($previous_group))
			{
				$previous_group = $despatch->product_group;
			}
			
			if ($previous_group != $despatch->product_group)
			{
				$this->customerorders[$previous_group]['total']['customer']			= 'Total';
				$this->customerorders[$previous_group]['total']['ontime_c']			= bcadd(round($group_ontime_c*100/$group_count, 2), 0);
				$this->customerorders[$previous_group]['total']['infull_c']			= bcadd(round($group_infull_c*100/$group_count, 2), 0);
				$this->customerorders[$previous_group]['total']['ontime_infull_c']	= bcadd(round($group_ontime_infull_c*100/$group_count, 2), 0);
				$this->customerorders[$previous_group]['total']['count']			= $group_count;
				
				$group_ontime_c			= 0;
				$group_infull_c			= 0;
				$group_ontime_infull_c	= 0;
				$group_count			= 0;
				
				$previous_group = $despatch->product_group;
			
			}

			$this->customerorders[$despatch->product_group][$despatch->slmaster_id]['customer']			= $despatch->customer;
			$this->customerorders[$despatch->product_group][$despatch->slmaster_id]['ontime_c']			= bcadd(round($despatch->ontime*100/$despatch->count, 2), 0);
			$this->customerorders[$despatch->product_group][$despatch->slmaster_id]['infull_c']			= bcadd(round($despatch->infull*100/$despatch->count, 2), 0);
			$this->customerorders[$despatch->product_group][$despatch->slmaster_id]['ontime_infull_c']	= bcadd(round($despatch->otif*100/$despatch->count, 2), 0);
			$this->customerorders[$despatch->product_group][$despatch->slmaster_id]['count']			= $despatch->count;
			
			$group_ontime_c			+= $despatch->ontime;
			$group_infull_c			+= $despatch->infull;
			$group_ontime_infull_c	+= $despatch->otif;
			$group_count			+= $despatch->count;
			
			$total_ontime_c			+= $despatch->ontime;
			$total_infull_c			+= $despatch->infull;
			$total_ontime_infull_c	+= $despatch->otif;
			$total_count			+= $despatch->count;
		}

		$this->customerorders[$previous_group]['total']['customer']			= 'Total';
		$this->customerorders[$previous_group]['total']['ontime_c']			= bcadd(round($group_ontime_c*100 / ($group_count ?: 1), 2), 0);
		$this->customerorders[$previous_group]['total']['infull_c']			= bcadd(round($group_infull_c*100/ ($group_count ?: 1), 2), 0);
		$this->customerorders[$previous_group]['total']['ontime_infull_c']	= bcadd(round($group_ontime_infull_c*100/ ($group_count ?: 1), 2), 0);
		$this->customerorders[$previous_group]['total']['count']			= $group_count;
		
		$this->customerorders['Grand Total']['total']['customer']			= '';
		$this->customerorders['Grand Total']['total']['ontime_c']			= bcadd(round($total_ontime_c*100/ ($total_count ?: 1), 2), 0);
		$this->customerorders['Grand Total']['total']['infull_c']			= bcadd(round($total_infull_c*100/($total_count ?: 1), 2), 0);
		$this->customerorders['Grand Total']['total']['ontime_infull_c']	= bcadd(round($total_ontime_infull_c*100/($total_count ?: 1), 2), 0);
		$this->customerorders['Grand Total']['total']['count']				= $total_count;
		
		return $this->customerorders;
		
	}

	function failureCodeSummary($sh)
	{
		
		$sh->setFields(
			array(
				"coalesce(cs_failurecode_id,0)||to_char(despatch_date, 'YYYYMM')",
				'cs_failurecode_id',
				"to_char(despatch_date, 'YYYY/MM') as period",
				'failurecode',
				'failure_description',
				"to_char(despatch_date, 'YYYY/MM')",
				'sum(1) as count'
			)
		);
		
		$cc=new ConstraintChain();
		$cc->add(new Constraint('despatch_date', '>', '(due_despatch_date)'));
		$cc->add(new Constraint('order_qty', '>', '(despatch_qty)'), 'OR');
		$sh->addConstraint($cc);
		
		$sh->setGroupBy(
			array(
				"coalesce(cs_failurecode_id,0)||to_char(despatch_date, 'YYYYMM')",
				'cs_failurecode_id',
				"to_char(despatch_date, 'YYYY/MM')",
				'failurecode',
				'failure_description'
			)
		);
							 
		$sh->setOrderBy(
			array(
				"to_char(despatch_date, 'YYYY/MM')",
				'failurecode',
				'failure_description'
			),
			array('DESC', 'ASC', 'ASC')
		);
		
		$this->load($sh);
		
		foreach ($this as $despatch)
		{
			$this->customerorders[$despatch->period][$despatch->cs_failurecode_id]['description']	= $despatch->failurecode . ' - ' . $despatch->failure_description;
			$this->customerorders[$despatch->period][$despatch->cs_failurecode_id]['count']			= $despatch->count;
			$this->customerorders[$despatch->period][$despatch->cs_failurecode_id]['period']		= $despatch->period;
		}
		
		return $this->customerorders;
		
	}

}

// end of CustomerServiceCollection.php
