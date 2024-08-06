<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POReceivedLineCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.8 $';
	
	public $field;

	function __construct($do = 'POReceivedLine', $tablename = 'po_receivedoverview')
	{
		parent::__construct($do, $tablename);

	}	

	function statusSummary($orderline_id)
	{
//		Returns an array of the statuses of POReceived lines
//      for the supplied orderline_id
//		i.e. there can be more than one received line (part delivery) for an order line
		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('orderline_id', '=', $orderline_id));
		
		$sh->setFields(array('status', 'sum(received_qty) as received_qty'));
		$sh->setGroupBy(array('status'));
		$sh->setOrderBy(array('status'));
		
		$rows = $this->load($sh, null, RETURN_ROWS);
		
		$status = array();
		
		foreach ($rows as $line)
		{
			$status[$line['id']] = $line['received_qty'];
		}
		
		return $status;
	}

	function getReceivedLines ($grn = '', $order_id = '')
	{
// Get the received orderlines for the specified goods received number and/or order id
		$sh = new SearchHandler($this, false);

		if (!empty($grn))
		{
			$sh->addConstraint(new Constraint('gr_number', '=', $grn));
		}

		if (!empty($order_id))
		{
			$sh->addConstraint(new Constraint('order_id', '=', $order_id));
		}

		$this->load($sh);
	}

	function getReceivedSum($months)
	{
		$startdate = fix_date('01/'.date('m/Y'));

		$startdate = strtotime((string) $startdate);

		$startdate = fix_date(date(DATE_FORMAT,strtotime("-3 months", $startdate)));

		$sh = new SearchHandler($this, false);

		$sh->addConstraint(new Constraint('received_date', '>=', $startdate));

		$this->load($sh);

		$receivedSum = array();

		foreach ($this as $received)
		{
			$period = date('M Y',strtotime($received->received_date));

			if (isset($receivedSum[$period]))
			{
				$receivedSum[$period] += $received->net_value;
			}
			else
			{
				$receivedSum[$period] = $received->net_value;
			}
		}

		foreach ($receivedSum as $key=>$value)
		{
			$receivedSum[$key] = sprintf('%0.2f', $value);
		}

		return $receivedSum;

	}
	
}

// End of POReceivedLineCollection
