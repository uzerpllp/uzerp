<?php
 
/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class SODespatchLine extends DataObject
{

	protected $version='$Revision: 1.11 $';
	
	protected $defaultDisplayFields = array('despatch_number'
											,'order_number'
											,'order_id'
											,'orderline_id'
											,'despatch_action'
											,'despatch_date'
											,'status'
											,'despatch_qty'
											,'customer'
											,'uom_name'
											,'stitem'
											,'invoice_number'
											,'invoice_id'
	                                        ,'description'
											);
	
	function __construct($tablename='so_despatchlines')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField='id';
		$this->orderby=array('despatch_number','orderline_id');
		$this->orderdir='DESC';
		
		// Define relationships
		$this->belongsTo('SOrder', 'order_id', 'order_number');
 		$this->belongsTo('SOrderLine', 'orderline_id', 'order_line');
 		$this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
 		$this->belongsTo('SOProductline', 'productline_id', 'description');
 		$this->belongsTo('STuom', 'stuom_id', 'uom_name');
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
 		$this->hasOne('WHAction', 'despatch_action', 'despatch_from');
 		$this->hasOne('SOrderLine', 'orderline_id', 'order_line_detail');
 		
		// Define field formats
		
		// Define formatters
		
		// Define validation
 		
		// Define enumerated types
 		$this->setEnum('status'
							,array('N'=>'New'
								  ,'D'=>'Despatched'
								  ,'X'=>'Cancelled'
								)
						);
		
		// Define rules for related items links
	
		// Define system defaults
	
	}
	
	/*
	 * createDespatchNote - Create a despatch note from the provided data
	 *
	 * @param array $data
	 *     see SODespatchLine::makeLine
	 *
	 * @return int|bool
	 *     Despatch Note id or False
	 */
	public static function createDespatchNote ($data, &$errors=array())
	{

		$db=DB::Instance();
		$db->startTrans();
		$result = TRUE;
		
		foreach ($data as $header)
		{
			
			$generator = new DespatchNoteNumberHandler();
			$despatch_number = $generator->handle(new SODespatchLine());
			
			foreach ($header as $line)
			{
				$line['despatch_number']=$despatch_number;
				$saveline=SODespatchLine::Factory($line,$errors,'SODespatchLine');
				
				if ($saveline)
				{
					$result=$saveline->save();
					if(!$result)
					{
						$errors[]=$db->ErrorMsg();
						break;
					}
				}
				
				$orderline=new SOrderLine();
				$orderline->load($line['orderline_id']);
				
				if ($orderline->isLoaded())
				{
					if (!is_null($orderline->delivery_note))
					{
						$errors[]='Order line already selected on DN '.$orderline->delivery_note;
						break;
					}
					elseif ($orderline->status!=$orderline->awaitingDespatchStatus()
							&& $orderline->status!=$orderline->pickedStatus())
					{
						$errors[]='Order line no longer available for despatch';
						break;
					}
					elseif (!$orderline->update($line['orderline_id'],'delivery_note',$despatch_number))
					{
						$errors[]='Error updating order line';
						break;
					}
				}
				else
				{
					$errors[]='Error loading order line';
					break;
				}
			}
		}
		
		if (count($errors) > 0)
		{
			$db->FailTrans();
			$result = FALSE;
		}
		
		$db->completeTrans();
        
		if (count($data) == 1)
		{
		  return $saveline->id;
		}
		
		return $result;
	}
	
	/**
	 * Build the line
	 *
	 */
	public static function makeLine($order, $orderline, &$errors = [])
	{

		$despatchline=array();
		if ($order->isLoaded()) {
			if ($order->customerdetails->accountStopped())
			{
				$errors['id'.$order->id]='Cannot despatch order '.$order->order_number.' ('.$order->customerdetails->name.') Account Stopped';
			}
			else
			{
				$despatchline['order_id']=$order->id;
				$despatchline['orderline_id']=$orderline->id;
				$despatchline['slmaster_id']=$order->slmaster_id;
				$despatchline['stuom_id']=$orderline->stuom_id;
		
				if ($orderline->stitem_id)
				{
					$despatchline['stitem_id']=$orderline->stitem_id;
				}
		
				if ($orderline->productline_id)
				{
					$despatchline['productline_id']=$orderline->productline_id;
				}
		
				$despatchline['despatch_qty']=$orderline->os_qty;
				$despatchline['despatch_date']=date(DATE_FORMAT);
				$despatchline['despatch_action']=$order->despatch_action;
				$despatchline['status']='N';
				$stitem=new STItem();
				$stitem->load($data['stitem_id']);
				$param=new GLParams();
				$net_mass_uom_id=$param->intrastat_net_mass();
		
				if ($stitem->isLoaded() && !empty($net_mass_uom_id))
				{
					$despatchline['net_mass']=$stitem->convertToUoM($despatchline['stuom_id'], $net_mass_uom_id, $despatchline['despatch_qty']);
				}
			}
		}
		
		if (empty($despatchline['net_mass']) || $despatchline['net_mass']===false)
		{
			$despatchline['net_mass']=0;
		}
		
		return $despatchline;
	}
	
	public static function getItems ($cc="")
	{
		$db=&DB::Instance();
		if($cc instanceof ConstraintChain)
		{
			$where = $cc->__toString();
		}
		else
		{
			$where = '1=1';
		}
		$query="SELECT stitem_id, sum(os_qty) as required
				  FROM so_orderlines
				 WHERE stitem_id is null
				   AND ".$where;
			   " GROUP BY stitem_id";
		$result= $db->Execute($query);
		return $result->getRows();
		
	}

	public function getStockBalance()
	{
		if ($this->stitem)
		{
    	    $locations=$this->despatch_from->rules_list('from_whlocation_id');
    		$balance=new STBalance();
    		return $balance->getStockBalance($this->stitem_id,$locations);
		}
    	
		return 0;
	}

	public function item_description()
	{
		return $this->order_line->description;
	}
	

	public function newStatus()
	{
		return 'N';
	}
		
	public function despatchStatus()
	{
		return 'D';
	}
		
	public function cancelStatus()
	{
		return 'X';
	}
		
}

// End of SODespatchLine
