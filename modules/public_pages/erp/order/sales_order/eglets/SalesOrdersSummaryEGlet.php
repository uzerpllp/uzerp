<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SalesOrdersSummaryEGlet extends SimpleEGlet
{

	protected $version = '$Revision: 1.11 $';

	protected $template = 'sorders_overview.tpl';
	
	function populate()
	{
		$pl = new PageList('top_sales_orders');
		
		$today		= fix_date(date(DATE_FORMAT));
		$weekend	= fix_date(date(DATE_FORMAT, strtotime("next Monday", strtotime($today))-1));
		$startdate	= fix_date('01/'.date('m/Y'));
		$monthend	= fix_date(date(DATE_FORMAT, strtotime("+1 months", strtotime($startdate))-1));
		$nextmonth	= fix_date(date(DATE_FORMAT, strtotime("+2 months", strtotime($startdate))-1));
		
		$orderline = DataObjectFactory::Factory('SOrderLine');
		
		$db = &DB::Instance();
		
		// Get overdue orders
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', $this->params['type']));
		$cc->add(new Constraint('status', 'in', "('N', 'R', 'S')"));
		$cc->add(new Constraint('due_despatch_date', '<', $db->qstr($today)));
		
		$customersales['Overdue'] = $orderline->getSum('base_net_value', $cc, 'so_linesoverview');
		
		// Get orders due today
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', $this->params['type']));
		$cc->add(new Constraint('status', 'in', "('N', 'R', 'S')"));
		$cc->add(new Constraint('due_despatch_date', '=', $db->qstr($today)));
		
		$customersales['Today'] = $orderline->getSum('base_net_value', $cc, 'so_linesoverview');
		
		// Get orders due this week
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', $this->params['type']));
		$cc->add(new Constraint('status', 'in', "('N', 'R', 'S')"));
		$cc->add(new Constraint('due_despatch_date', '>', $db->qstr($today)));
		$cc->add(new Constraint('due_despatch_date', '<=', $db->qstr($weekend)));
		
		$customersales['This Week'] = $orderline->getSum('base_net_value', $cc, 'so_linesoverview');

		// Get orders due this month
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', $this->params['type']));
		$cc->add(new Constraint('status', 'in', "('N', 'R', 'S')"));
		$cc->add(new Constraint('due_despatch_date', '>', $db->qstr($weekend)));
		$cc->add(new Constraint('due_despatch_date', '<=', $db->qstr($monthend)));
		
		$customersales['This Month'] = $orderline->getSum('base_net_value', $cc, 'so_linesoverview');
		
		// Get orders due next month
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('type', '=', $this->params['type']));
		$cc->add(new Constraint('status', 'in', "('N', 'R', 'S')"));
		$cc->add(new Constraint('due_despatch_date', '>', $db->qstr($monthend)));
		$cc->add(new Constraint('due_despatch_date', '<=', $db->qstr($nextmonth)));
		
		$customersales['Next Month'] = $orderline->getSum('base_net_value', $cc, 'so_linesoverview');
		
		$this->title			= 'Title';
		$this->contents['main'] = $customersales;
		$this->contents['type'] = $this->params['type'];
	}
	
}

// End of SalesOrdersSummaryEGlet
