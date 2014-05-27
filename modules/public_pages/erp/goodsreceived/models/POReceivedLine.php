<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POReceivedLine extends DataObject {

	protected $version = '$Revision: 1.19 $';
	
	protected $defaultDisplayFields = array('order_number'
											,'gr_number'
											,'delivery_note'
											,'order_id'
											,'orderline_id'
											,'supplier'
											,'received_date'
											,'received_qty'
											,'uom_name'
											,'item_description'
											,'stitem'
											,'status'
											,'currency'
											,'net_value'
											,'invoice_id'
											,'invoice_number'
											,'plmaster_id'
											);
	
	function __construct($tablename = 'po_receivedlines') 
	{
		parent::__construct($tablename);
		
		$this->idField			= 'id';
		$this->identifierField	= 'gr_number';
		$this->orderby			= array('received_date', 'gr_number');
		$this->orderdir			= array('DESC', 'DESC');
		
 		$this->belongsTo('POrder', 'order_id', 'order_number');
// 		$this->belongsTo('POrderLine', 'orderline_id', 'order_line');
 		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier');
 		$this->belongsTo('POProductline', 'productline_id', 'description'); 
 		$this->belongsTo('STuom', 'stuom_id', 'uom_name'); 
 		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 
 		
 		$this->hasOne('POrderLine', 'orderline_id', 'order_line');
 		
 		$this->setEnum('status'
							,array('A'=>'Accrued'
								  ,'R'=>'Received'
								  ,'I'=>'Invoiced'
								  ,'W'=>'Written Off'
								  ,'X'=>'Cancelled'
								  )
						);
		
	}
	
	/**
	 * Build the line
	 *
	 */
	public static function makeLine($data,&$errors)
	{
		$line = DataObjectFactory::Factory('POrderLine');
		
		foreach($data as $key=>$value)
		{
			$line->$key = $value;
		}
		
		$line->usercompanyid = EGS_COMPANY_ID;
		return $line;
	}
	
	public static function getItems ($cc = "")
	{
		$db = &DB::Instance();
		
		if($cc instanceof ConstraintChain)
		{
			$where = $cc->__toString();
		}
		else
		{
			$where = '1=1';
		}
		
		$query="SELECT stitem_id, sum(os_qty) as required
				  FROM po_orderlines
				 WHERE stitem_id is null
				   AND ".$where;
			   " GROUP BY stitem_id";
		
		$result = $db->Execute($query);
		
		return $result->getRows();
		
	}
	
	public function accrueLine($_ids = '', &$errors = array(), $_reverse_accrual = FALSE)
	{
		if (empty($_ids) || !is_array($_ids))
		{
			return FALSE;
		}
		
		$db = &DB::Instance();
		
		$db->StartTrans();
		
		$poreceivedline = DataObjectFactory::Factory('POReceivedLine');
		
		$poreceivedlines = new POReceivedLineCollection($poreceivedline);
		
		$sh = new SearchHandler($poreceivedlines, FALSE);
		
		$sh->addConstraint(new Constraint('id', 'in', '(' . implode(',', array_keys($_ids)) . ')'));
		
		$glparams = DataObjectFactory::Factory('GLParams');
		
		$accrual_control = $glparams->accruals_control_account();
		
		$result = TRUE;
		
		if (!empty($accrual_control))
		{
			$cost_centre = $glparams->balance_sheet_cost_centre();
			
			$sh->setFields(array('id'
								,'glaccount_id'
								,'glcentre_id'
								,'item_description'
								,'net_value'
								,'order_number'
								,'rate'
								,'received_date'));
			
			$rows = $poreceivedlines->load($sh, null, RETURN_ROWS);
			
			$gltransactions = GLTransaction::makeFromGRN(array('control_glaccount_id'	=> $accrual_control
															  ,'control_glcentre_id'	=> $cost_centre
															  ,'reverse_accrual'		=> $_reverse_accrual)
														,$rows, $errors);
			
			if (count($errors) > 0)
			{
				$result = FALSE;
				$errors[] = 'Error saving GL Transaction : '.$db->ErrorMsg();
			}
			else
			{
				// Save the GL Transactions and update the balances
				if (!GLTransaction::saveTransactions($gltransactions, $errors))
				{
					$result = FALSE;
					$errors[] = 'Error saving GL Transaction : '.$db->ErrorMsg();
				}
			}
		}
		
		// Now update the received lines status to accrued
		if ($result !== FALSE)
		{
			if ($_reverse_accrual)
			{
				$result = $poreceivedlines->update('status', $poreceivedline->writeOffStatus(), $sh);
			}
			else
			{
				$result = $poreceivedlines->update('status', $poreceivedline->accrualStatus(), $sh);
			}
			
			if ($result !== FALSE && $result <> count($_ids))
			{
				$errors[] = 'Updated '.$result.' expected '.count($_ids);
				$result = FALSE;
			}
		}
		
		if ($result === FALSE)
		{
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
	}
	
	public function accrualStatus() {
		return 'A';
	}

	public function cancelStatus() {
		return 'X';
	}

	public function invoiceStatus() {
		return 'I';
	}

	public function receiveStatus() {
		return 'R';
	}

	public function writeOffStatus() {
		return 'W';
	}

	public function isAccrued() {
		return ($this->status=='A');
	}

	public function isCancelled() {
		return ($this->status=='X');
	}

	public function isInvoiced() {
		return ($this->status=='I');
	}

	public function isReceived() {
		return ($this->status=='R');
	}

	public function isWrittenOff() {
		return ($this->status=='W');
	}

	public function getUnmatchedInvoices ()
	{
		$invoice = DataObjectFactory::Factory('PInvoice');
		
		$invoice->_tablename='pi_linesoverview';
		
		$invoice->identifierField="'Inv.No. '|| invoice_number ||' Date '|| invoice_date ||' - Value '|| net_value ||' - Description '|| description";
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('plmaster_id', '=', $this->plmaster_id));
		$cc->add(new Constraint('purchase_order_id', 'is', 'NULL'));
		$cc->add(new Constraint('transaction_type', '=', 'I'));
//		$cc->add(new Constraint('invoice_date', '>', $this->received_date));

		return $invoice->getAll($cc);
	}
	
	public function invoicedQty($_orderline_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('status', '=', $this->invoiceStatus()));
		$cc->add(new Constraint('orderline_id', '=', $_orderline_id));
		
		return $this->getSum('received_qty', $cc);
	}

	/*
	 * static function getReceivedQuantity
	 * 
	 * Get the received quantity for this orderline
	 * if the grn is supplied, exclude this grn from the sum
	 */
	public function getReceivedQty ($_orderline_id, $_gr_number = '')
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('status', '!=', $this->cancelStatus()));
		$cc->add(new Constraint('orderline_id', '=', $_orderline_id));
		
		if (!empty($_gr_number))
		{
			$cc->add(new Constraint('gr_number', '!=', $_gr_number));
		}
		
		return $this->getSum('received_qty', $cc);
		
	}

}

// End of POReceivedLine
