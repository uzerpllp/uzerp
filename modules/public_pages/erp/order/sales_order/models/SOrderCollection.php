<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrderCollection extends DataObjectCollection {
	
	protected $version = '$Revision: 1.15 $';
	
	public $field;

	function __construct($do = 'SOrder', $tablename = 'so_headeroverview')
	{
		
		parent::__construct($do, $tablename);
	
		$this->view = '';

	}

	public function getItems(&$sh, $cc = "")
	{
		
		if ($sh instanceof SearchHandler)
		{
			
			$DisplayFields = array(
				'stitem_id',
				'stitem',
				'uom_name',
				'required'
			);
			
			$sh->setOrderby('stitem');
			$sh->setFields($DisplayFields);
			
			if (!empty($cc) && ($cc instanceof ConstraintChain))
			{
				$sh->addConstraintChain($cc);
			}
			
		}
		
		$this->_tablename = 'so_items';

	}

	public function getItemDates($cc = "")
	{
		
		$sh = new SearchHandler($this, FALSE);
		
		$DisplayFields = array(
			'due_date',
			'stitem_id',
			'stitem',
			'required'
		);
		
		$sh->setOrderby('due_date');
		$sh->setFields($DisplayFields);
		
		if (!empty($cc) && ($cc instanceof ConstraintChain))
		{
			$sh->addConstraintChain($cc);
		}
		
		$this->_tablename = 'so_itemdates';
		$this->load($sh);
		
		return $this;
		
	}

	public function getItemOrders($cc = "")
	{

		$sh = new SearchHandler($this, FALSE);
		
		$DisplayFields = array(
			'id',
			'stitem_id',
			'due_despatch_date',
			'stitem',
			'order_number',
			'line_number',
			'order_id',
			'customer',
			'slmaster_id',
			'stuom',
			'required',
			'delivery_note',
			'despatch_action',
			'status',
			'account_status',
			'status'
		);
		
//		$sh->setOrderby(
//			array('order_number', 'line_number'),
//			array('ASC', 'ASC')
//		);
		
		$sh->setFields($DisplayFields);
		
		if (!empty($cc) && ($cc instanceof ConstraintChain))
		{
			$sh->addConstraintChain($cc);
		}
		
		$this->_tablename = 'so_itemorders';
		$this->load($sh);
		
		return $this;
		
	}

}

// end of SOrderCollection.php