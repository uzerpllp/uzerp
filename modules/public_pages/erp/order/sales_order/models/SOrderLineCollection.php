<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrderLineCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.18 $';
	
	public $field;
		
	protected $customerorders = array();
	
	protected $customersales = array();
	
	function __construct($do = 'SOrderLine', $tablename = 'so_linesoverview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby = 'line_number';
	}
	
	function getOrderItemSummary ($period, $type = '', $page = '', $perpage = '')
	{
		$sh = new SearchHandler($this, false);
		
		if($type!='')
		{
			$sh->addConstraint(new Constraint('type', '=', $type));
		}
		
		if (!empty($page))
		{
			if (empty($perpage))
			{
				$perpage = 9;
			}
			
			$sh->setLimit($perpage, ($page-1)*$perpage);
			$sh->perpage=$perpage;
		}
		
		$fields = array('stitem');
		
		$sh->setGroupBy($fields);
		
		$sh->setOrderby($fields);
		
		$fields[] = 'sum(os_qty) as qty';
		$fields[] = 'sum(base_net_value) as value';
		
		$sh->setFields($fields);
		
		$sh->addConstraint(new Constraint('status', 'in', "('N', 'R', 'S')"));
		
		$today = fix_date(date(DATE_FORMAT));
		
		$currentmonthstart = fix_date('01/'.date('m/Y'));
		
		switch (prettify($period))
		{
			case ('Overdue'):
				$startdate	= fix_date('01/01/1970');
				$enddate	= fix_date(date(DATE_FORMAT,strtotime("-1 day",strtotime($today))));
				break;
			case ('Today'):
				$startdate	= $today;
				$enddate	= $today;
				break;
			case ('This Week'):
				$startdate	= fix_date(date(DATE_FORMAT,strtotime("+1 days",strtotime($today))));
				$enddate	= fix_date(date(DATE_FORMAT,strtotime("-1 day",strtotime("next Monday",strtotime($today)))));;
				break;
			case ('This Month'):
				$startdate	= fix_date(date(DATE_FORMAT,strtotime("next Monday",strtotime($today))));
				$enddate	= fix_date(date(DATE_FORMAT,strtotime("-1 day",strtotime("+1 months",strtotime($currentmonthstart)))));
				break;
			case ('Next Month'):
				$startdate	= fix_date(date(DATE_FORMAT,strtotime("+1 months",strtotime($currentmonthstart))));
				$enddate	= fix_date(date(DATE_FORMAT,strtotime("-1 day",strtotime("+2 months",strtotime($currentmonthstart)))));
				break;
		}
		
		$sh->addConstraint(new Constraint('due_despatch_date', 'between', "'".$startdate."' and '".$enddate."'"));
		
		$this->load($sh);
		
		// Construct dynamic title ('Orders', 'Quotes', default)
		if($type=='O')
		{
			$this->customersales = array('title'=>'Orders, '.prettify($period), 'items'=>array());
		}
		elseif($type=='Q')
		{
			$this->customersales = array('title'=>'Quotes, '.prettify($period), 'items'=>array());
		}
		else
		{
			$this->customersales = array('title'=>prettify($period), 'items'=>array());
		}
		
		foreach ($this as $order)
		{
			$this->customersales['items'][$order->id]['value']	= $order->value;
			$this->customersales['items'][$order->id]['qty']	= $order->qty;
		}
		
		$this->customersales['page']		= $page;
		$this->customersales['perpage']		= $perpage;
		$this->customersales['num_pages']	= ($this->num_pages==0?1:$this->num_pages);
		$this->customersales['period']		= $period;
		$this->customersales['type']		= $type;
		$this->customersales['controller']	= 'Sorders';
		
		return $this->customersales;
	}

	function getTopOrders($top = 10, $type = 'customer')
	{
		
		$startdate	= fix_date('01/'.date('m/Y'));
		$enddate	= fix_date(date(DATE_FORMAT,strtotime("-1 days",strtotime("+1 months",strtotime($startdate)))));
		
		$sh = new SearchHandler($this, false);

		$fields = array();
		
		switch ($type) {
			case ('customer'):
				$fields[]	= 'customer';
				$sumby		= 'base_net_value';
				break;
			case ('item by qty'):
				$fields[]	= 'stitem';
				$sumby		= 'order_qty';
				$sh->addConstraint(new Constraint('stitem', 'is not', 'NULL'));
				break;
			case ('item by value'):
				$fields[]	= 'stitem';
				$sumby		= 'base_net_value';
				$sh->addConstraint(new Constraint('stitem', 'is not', 'NULL'));
				break;
		}
		
		$sh->setGroupBy($fields);
		
		$sh->setOrderby($fields);
		
		$fields[]='sum('.$sumby.') as value';
		
		$sh->setFields($fields);
		
		$sh->addConstraint(new Constraint('type', '=', 'O'));
		$sh->addConstraint(new Constraint('order_date', 'between', "'".$startdate."' and '".$enddate."'"));
		
		$this->load($sh);

		$typesarray = array('customer'		=> 'By Customer'
						   ,'item by qty'	=> 'By Item Quantity'
						   ,'item by value'	=> 'By Item Value');
		
		$this->customerorders = array('source'		=> 'orders'
									 ,'controller'	=> 'sorders'
									 ,'submodule'	=> 'sales_order'
									 ,'types'		=> $typesarray
									 ,'type'		=> $type
									 ,'details'		=> array());

		$data = array();
		
		foreach ($this as $order)
		{
			$data[$order->id] = $order->value;
		}
		
		arsort($data, SORT_NUMERIC);
								   
		$count = 0;
		
		foreach ($data as $key=>$value)
		{
			if ($count<$top)
			{
				$this->customerorders['details'][$key] = $value;
			}
			else
			{
				break;
			}
			
			$count++;
		}
								   
		return $this->customerorders;
	}

	public function ordersForInvoicing ()
	{
		$sh = new SearchHandler($this,false);
		
		$DisplayFields = array('order_id'
							  ,'order_number'
							  );
		
		$sh->setOrderby(array('order_number')
					   ,array('ASC'));
		
		$sh->setFields($DisplayFields);
		
		$sh->setGroupBy($DisplayFields);
		
		$order = DataObjectFactory::Factory('Sorder');
		
		$sh->addConstraint(new Constraint('status', '=', $order->despatchStatus()));
		
		$this->load($sh);
		
		return $this;
	}
	
}

// End of SOrderLineCollection
