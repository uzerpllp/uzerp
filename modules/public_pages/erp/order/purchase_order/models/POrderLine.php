<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POrderLine extends SPOrderLine
{

	protected $version = '$Revision: 1.33 $';
	
	protected $action = array();

	public $whaction_id;
	public $from_location_id;
	public $to_location_id;
	public $to_location;
	public $from_bin_list	= array();
	public $to_bin_list		= array();
	
	protected $defaultDisplayFields = array(
		'order_id',
		'order_number',
		'stitem_id',
		'stitem',
		'description',
		'due_delivery_date',
		'status',
		'order_qty',
		'os_qty',
		'revised_qty',
		'del_qty',
		'price',
		'uom_name'
	);
	
	protected $defaultsNotAllowed = array(
		'order_id',
		'line_number',
		'rate',
		'del_qty',
		'os_qty',
		'base_net_value',
		'twin_net_value',
		'twin_currency_id',
		'twin_rate',
		'actual_delivery_date',
		'gr_note'
	);
											
	function __construct($tablename = 'po_lines')
	{

		// Register non-persistent attributes
		$this->setAdditional('product_search');

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('line_number', 'order_id');
		
		// Define relationships
		$this->belongsTo('POrder', 'order_id', 'order_number');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
 		$this->belongsTo('POProductline', 'productline_id', 'product_description'); 
 		$this->belongsTo('STuom', 'stuom_id', 'uom_name'); 
 		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 
 		$this->hasOne('POrder', 'order_id', 'header');

		// Define field formats
 		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));

		// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));
		
		// Define enumerated types
		$this->setEnum(
			'status',
			array(
				'N'	=> 'New',
				'A'	=> 'Awaiting Delivery',
				'P'	=> 'Part Received',
				'H'	=> 'Hold',
				'X'	=> 'Cancelled',
				'R'	=> 'Received',
				'I'	=> 'Invoiced'
			)
		);
		
		// Define system defaults
		$this->getField('order_qty')->setDefault('0');
		$this->getField('os_qty')->setDefault('0');
		$this->getField('revised_qty')->setDefault('0');
		$this->getField('del_qty')->setDefault('0');
		$this->getField('price')->setDefault('0.00');
		$this->getField('net_value')->setDefault('0.00');
		$this->getField('base_net_value')->setDefault('0.00');
		$this->getField('twin_net_value')->setDefault('0.00');
		$this->getField('line_discount')->setDefault('0');
		$this->getField('status')->setDefault('N');
						
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
 		$this->getField('price')->setFormatter(new CurrencyFormatter($this->_data['currency_id'], 4));
 		$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
 		
	}

	public function delete ()
	{

		$flash = Flash::Instance();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$result = parent::delete();
		// Save the header to update the header totals
		// and also check authorisation limits
		if ($result && !$this->header->save())
		{
			$result = false;
			$flash->addError('Error updating header');
		}
		
		if ($result)
		{
			// Now update the line numbers of following lines
			$porderlines = new PorderLineCollection($this);
			
			$sh = new SearchHandler($porderlines, false);
			
			$sh->addConstraint(new Constraint('order_id', '=', $this->order_id));
			$sh->addConstraint(new Constraint('line_number', '>', $this->line_number));
			
			if ($porderlines->update('line_number', '(line_number-1)', $sh)===false)
			{
				$flash->addError('Error updating line numbers '.$db->ErrorMsg());
				$result = false;
			}
		}
		
		if ($result===false) {
			$db->FailTrans();
		}			
		
		$db->CompleteTrans();
		return $result;
		
	}
	
	public static function Factory (POrder $header, $line_data, &$errors = array())
	{
				
		if (empty($line_data['order_id']))
		{
			$line_data['order_id'] = $header->id;
		}
		
		if (empty($line_data['line_number']))
		{
			$line_data['line_number'] = $header->getNextLineNumber();
		}
		
		if($line_data['revised_qty']>0 && $line_data['price']>0)
		{
			if (empty($line_data['id']))
			{
				// New Line
				$line_data['order_qty']	= $line_data['os_qty']=$line_data['revised_qty'];
				$line_data['status']	= 'N';
			}
			else
			{
				// Amended Line
				if ($line_data['status']=='N')
				{
					$line_data['os_qty'] = $line_data['revised_qty'];
				}
				elseif ($line_data['revised_qty'] >= $line_data['del_qty'])
				{
					$line_data['os_qty'] = $line_data['revised_qty']-$line_data['del_qty'];
					
					if ($line_data['os_qty']==0)
					{
						$line_data['status'] = 'R';
					}
				}
				else
				{
					$errors[] = 'Revised Quantity less than Delivered Quantity';
					return false;
				}
			}
		}
		else
		{
			$errors[] = 'Zero quantity or net value';
			return false;
		}
			
		$line_data['item_description'] = $line_data['description'];
		
		if ($line_data['productline_id']==-1)
		{
			$line_data['productline_id'] = '';
			$line_data['stitem_id']		 = '';
		}
		elseif (!empty($line_data['productline_id']))
		{
			$productline = DataObjectFactory::Factory('POProductline');
			
			$productline->load($line_data['productline_id']);
			
			if ($productline->isLoaded())
			{
				$productlineheader = $productline->product_detail;
				
				if (is_null($productlineheader->stitem_id))
				{
					$line_data['item_description']	= $productline->getDescription();
					$line_data['stitem_id']			= '';
				}
				else
				{
					$line_data['item_description']	= $productlineheader->stitem;
					$line_data['stitem_id']			= $productlineheader->stitem_id;
				}
				
				if (empty($line_data['price']))
				{
					$line_data['price'] = $productline->getPrice('', '', $productline->slmaster_id);
				}
				
				if (empty($line_data['glaccount_id']))
				{
					$line_data['glaccount_id'] = $productline->glaccount_id;
				}
				
				if (empty($line_data['glcentre_id']))
				{
					$line_data['glcentre_id'] = $productline->glcentre_id;
				}
				
				if (empty($line_data['stuom_id']))
				{
					$line_data['stuom_id'] = $productlineheader->stuom_id;
				}
				
				if (empty($line_data['tax_rate_id']))
				{
					$line_data['tax_rate_id'] = $productlineheader->tax_rate_id;
				}
			}
			// Check if glaccount_centre_id exists - can be any value including null
			if (!array_key_exists('glaccount_centre_id', $line_data))
			{
				$line_data['glaccount_centre_id'] = GLAccountCentre::getAccountCentreId($line_data['glaccount_id'], $line_data['glcentre_id'], $errors);
			}
			
			if (empty($line_data['net_value']))
			{
				$line_data['net_value'] = bcmul($line_data['price'], $line_data['revised_qty']);
			}
		}
		
		if (empty($line_data['description']))
		{
			$line_data['description'] = $line_data['item_description'];
		}
		
		$line_data['line_discount']		= 0;
		$line_data['currency_id']		= $header->currency_id;
		$line_data['rate']				= $header->rate;
		$line_data['twin_currency_id']	= $header->twin_currency_id;
		$line_data['twin_rate']			= $header->twin_rate;
		$line_data['base_net_value']	= round(bcdiv($line_data['net_value'],$line_data['rate'],4),2);
		$line_data['twin_net_value']	= round(bcmul($line_data['base_net_value'],$line_data['twin_rate'],4),2);
		
		if (empty($line_data['due_delivery_date']))
		{
			$line_data['due_delivery_date'] = un_fix_date($header->due_date);
		}
		
		return parent::Factory($line_data, $errors, 'POrderLine');
	}
	
	public function save($porder = null, &$errors = array())
	{

		$db = DB::Instance();
		
		$db->startTrans();
		
		$result = parent::save();
		
		if ($result && !is_null($porder))
		{
			// Need to update the header totals and status
			// and reset the header due date to latest due date on the order lines
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('order_id', '=', $this->order_id));
			
			$porder->due_date=$this->getMax('due_delivery_date', $cc);
			
			$result = $porder->save($errors);
		}
		
		if ($result===false)
		{
			$flash = Flash::Instance();
			$flash->addError($db->ErrorMsg());
			
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public function awaitingDeliveryStatus()
	{
		return 'A';
	}

	public function cancelStatus()
	{
		return 'X';
	}

	public function newStatus()
	{
		return 'N';
	}

	public function invoiceStatus()
	{
		return 'I';
	}

	public function partReceivedStatus()
	{
		return 'P';
	}
	
	public function receivedStatus()
	{
		return 'R';
	}
	
	public function lineAwaitingDelivery()
	{
		return ($this->status=='A');
	}
	
	public function lineCancelled()
	{
		return ($this->status=='X');
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

	public static function getSupplier ($orderid)
	{
		$order = DataObjectFactory::Factory('POrder');
		
		$order->load($orderid);
		
		$supplierName = '';
		
		if ($order)
		{
			$supplier = DataObjectFactory::Factory('PLSupplier');
			$supplier->load($order->plmaster_id);
			
			if ($supplier)
			{
				$supplierName=$supplier->name;
			}
		}
		
		return $supplierName;
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
		
		$query = "SELECT stitem_id, sum(os_qty) as required
				  FROM po_orderlines
				 WHERE stitem_id is null
				   AND ".$where;
			   " GROUP BY stitem_id";
		
		$result = $db->Execute($query);
		
		return $result->getRows();
		
	}

	public function getItemAction($action)
	{
		if (!is_null($this->stitem_id))
		{
			$stitem = DataObjectFactory::Factory('STItem');
			
			$stitem->load($this->stitem_id);
			
			$staction = $stitem->getAction($action);
			
			if (!is_null($staction))
			{
// Get the transfer rule for the action
				$transrule = DataObjectFactory::Factory('WHTransferrule');
				
				$cc = new ConstraintChain();
				$cc->add(new Constraint('whaction_id','=', $staction));
				
				$transrule->loadBy($cc);
				
// Get from/to location/bins for the transfer rule
				$this->action[$action]['from_whlocation_id'] = $transrule->from_whlocation_id;
				
				$from_location = DataObjectFactory::Factory('WHLocation');
				$from_location->load($transrule->from_whlocation_id);
				
				if ($from_location && $from_location->isBinControlled() )
				{
					$this->action[$action]['from_binlist'] = $from_location->getBinList();
				}
				
				$this->action[$action]['to_whlocation_id'] = $transrule->to_whlocation_id;
				
				$to_location = DataObjectFactory::Factory('WHLocation');
				$to_location->load($transrule->to_whlocation_id);
				
				if ($to_location && $to_location->isBinControlled() )
				{
					$this->action[$action]['to_binlist'] = $to_location->getBinList();
				}
				
				return $staction;
			}	
		}
		return '';
	}

	public function getAction()
	{
		if (!is_null($this->stitem_id))
		{
			if (!is_null($this->receive_action))
			{
// Get the transfer rule for the action
				$transrule = DataObjectFactory::Factory('WHTransferrule');
				
				$cc = new ConstraintChain();
				
				$cc->add(new Constraint('whaction_id','=', $this->receive_action));
				
				$transrule->loadBy($cc);
				
// Get from/to location/bins for the transfer rule
				$this->action[$this->receive_action]['from_whlocation_id'] = $transrule->from_whlocation_id;
				
				$from_location = DataObjectFactory::Factory('WHLocation');
				$from_location->load($transrule->from_whlocation_id);
				
				if ($from_location && $from_location->isBinControlled() )
				{
					$this->action[$this->receive_action]['from_binlist'] = $from_location->getBinList();
				}
				else
				{
					$this->action[$this->receive_action]['from_binlist'] = '';
				}
				
				$this->action[$this->receive_action]['to_whlocation_id'] = $transrule->to_whlocation_id;
				
				$to_location = DataObjectFactory::Factory('WHLocation');
				$to_location->load($transrule->to_whlocation_id);
				
				if ($to_location && $to_location->isBinControlled() )
				{
					$this->action[$this->receive_action]['to_binlist'] = $to_location->getBinList();
				}
				else
				{
					$this->action[$this->receive_action]['to_binlist'] = '';
				}
			}	
		}
		
		return '';
	}

	public function getFromLocation($action)
	{
		if (isset($this->action[$action]))
		{
			return $this->action[$action]['from_whlocation_id'];
		}
		else
		{
			$this->getItemAction($action);
		}
		
		if (isset($this->action[$action]))
		{
			return $this->action[$action]['from_whlocation_id'];
		}
		else
		{
			return '';
		}
	}
	
	public function getToLocation($action)
	{
		if (isset($this->action[$action]))
		{
			return $this->action[$action]['to_whlocation_id'];
		}
		else
		{
			$this->getItemAction($action);
		}
		
		if (isset($this->action[$action]))
		{
			return $this->action[$action]['to_whlocation_id'];
		}
		else
		{
			return '';
		}
	}

	public function getFromBin($action)
	{
		if (isset($this->action[$action]['from_binlist']))
		{
			return $this->action[$action]['from_binlist'];
		}
		else
		{
			$this->getItemAction($action);
		}
		
		if (isset($this->action[$action]['from_binlist']))
		{
			return $this->action[$action]['from_binlist'];
		}
		else
		{
			return '';
		}
	}
	
	public function getToBin($action)
	{
		if (isset($this->action[$action]['to_binlist']))
		{
			return $this->action[$action]['to_binlist'];
		}
		else
		{
			$this->getItemAction($action);
		}
		
		if (isset($this->action[$action]['to_binlist']))
		{
			return $this->action[$action]['to_binlist'];
		}
		else
		{
			return '';
		}
	}
	
}

// end of POrderLine.php
