<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SOrderLine extends SPOrderLine {

	protected $version = '$Revision: 1.38 $';

	protected $defaultDisplayFields = array('order_id'
											,'order_number'
											,'stitem_id'
											,'description'
											,'due_despatch_date'
											,'status'
											,'order_qty'
											,'os_qty'
											,'revised_qty'
											,'del_qty'
											,'price'
											,'stuom_id'
											,'uom_name'
											,'tax_rate_id'
	                                        ,'note'
											);

	protected $defaultsNotAllowed = array('order_id'
 										 ,'line_number'
 										 ,'rate'
										 ,'del_qty'
										 ,'os_qty'
										 ,'base_net_value'
 										 ,'twin_net_value'
 										 ,'twin_currency_id'
 										 ,'twin_rate'
 										 ,'actual_despatch_date'
 										 ,'delivery_note'
										 );

	function __construct($tablename='so_lines')
	{
// Register non-persistent attributes
		$this->setAdditional('product_search');

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= array('line_number', 'order_id');

// Define relationships
 		$this->hasOne('SOrder', 'order_id', 'header');
 		$this->belongsTo('SOrder', 'order_id', 'order_number');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin');
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre');
 		$this->belongsTo('SOProductline', 'productline_id', 'product_description');
 		$this->belongsTo('STuom', 'stuom_id', 'uom_name');
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
  		$this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate');

// Define field formats
 		$this->getField('price')->setFormatter(new PriceFormatter());
		$this->getField('net_value')->setFormatter(new PriceFormatter());
		$this->getField('base_net_value')->setFormatter(new PriceFormatter());

// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre',array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));

// Define enumerated types
		$this->setEnum('status'
							,array('N'=>'New'
//								  ,'A'=>'Advise Despatch'
								  ,'S'=>'Picked'
								  ,'R'=>'Ready for Despatch'
								  ,'P'=>'Part Despatched'
								  ,'D'=>'Despatched'
								  ,'H'=>'Hold'
								  ,'X'=>'Cancelled'
								  ,'I'=>'Invoiced'
								)
						);

		$sorder_model = new SOrder;
		$this->setEnum(
			'type',
			$sorder_model->enums['type']
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
		$this->getField('line_value')->setDefault('0.00');
		$this->getField('line_tradedisc_percentage')->setDefault('0');
		$this->getField('line_qtydisc_percentage')->setDefault('0');
		$this->getField('status')->setDefault('N');

	}

	public function delete ()
	{

		$flash = Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();

		$result = parent::delete();

		// Save the header to update the header totals
		if ($result && !$this->header->save())
		{
			$result = false;
			$flash->addError('Error updating header');
		}

		if ($result)
		{
			// Now update the line numbers of following lines
			$sorderlines = new SorderLineCollection($this);

			$sh = new SearchHandler($sorderlines, false);

			$sh->addConstraint(new Constraint('order_id', '=', $this->order_id));
			$sh->addConstraint(new Constraint('line_number', '>', $this->line_number));

			if ($sorderlines->update('line_number', '(line_number-1)', $sh)===false)
			{
				$flash->addError('Error updating line numbers '.$db->ErrorMsg());
				$result=false;
			}
		}

		if ($result===false)
		{
			$db->FailTrans();
		}

		$db->CompleteTrans();
		return $result;

	}

	public static function Factory (SOrder $header, $line_data, &$errors)
	{

		if (empty($line_data['order_id']))
		{
			$line_data['order_id'] = $header->id;
		}

		if (empty($line_data['line_number']))
		{
			$line_data['line_number'] = $header->getNextLineNumber();
		}

		$line_data['item_description'] = $line_data['description'];

		if ($line_data['productline_id']==-1)
		{
			$line_data['productline_id'] = '';
			$line_data['stitem_id']		 = '';
		}
		else
		{
			$productline = DataObjectFactory::Factory('SOProductline');

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
		if($line_data['revised_qty']>0 && $line_data['price']>0)
		{
			if (empty($line_data['id']))
			{
				// New Line
				$line_data['order_qty']	= $line_data['os_qty'] = $line_data['revised_qty'];
				$line_data['status']	= 'N';
			}
			else
			{
				// Amended Line
				if ($line_data['status']=='N')
				{
					$line_data['os_qty'] = $line_data['revised_qty'];
				}
			}
		}
		else
		{
			$errors[] = 'Zero quantity or net value';
		}

		if (count($errors) > 0)
		{
			return false;
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
		$line_data['base_net_value']	= bcadd(round(bcdiv($line_data['net_value'], $line_data['rate'], 4), 2), 0);
		$line_data['twin_net_value']	= bcadd(round(bcmul($line_data['base_net_value'], $line_data['twin_rate'], 4), 2), 0);

		if (empty($line_data['due_delivery_date']))
		{
			$line_data['due_delivery_date'] = un_fix_date($header->due_date);
		}

		if (empty($line_data['due_despatch_date']))
		{
			$line_data['due_despatch_date'] = un_fix_date($header->despatch_date);
		}

		return parent::Factory($line_data, $errors, 'SOrderLine');
	}

	public function save($sorder=null)
	{

		$db = DB::Instance();

		$db->startTrans();

		$result = parent::save();

		if ($result && !is_null($sorder))
		{
			// Need to update the header totals and status
			// and reset the header due date to latest due date on the order lines
			$cc = new ConstraintChain();

			$cc->add(new Constraint('order_id', '=', $this->order_id));

			$sorder->despatch_date	= $this->getMax('due_despatch_date', $cc);
			$sorder->due_date		= $this->getMax('due_delivery_date', $cc);

			$result=$sorder->save();
		}

		if ($result===false)
		{
			$flash = Flash::Instance();
			$flash->addError('Error saving Order Line : '.$db->ErrorMsg());
			$db->FailTrans();
		}

		$db->CompleteTrans();

		return $result;

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

		$result = $db->Execute($query);

		return $result->getRows();

	}

	public function getProductGroup ()
	{
		$soproductline = DataObjectFactory::Factory('SOProductLine');

		$soproductline->load($this->productline_id);

		return $soproductline->getProductGroup();
	}

	public function calcTax ($tax_status_id, $payment_term)
	{
		//tax  (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
		//this function is a wrapper to a call to a config-dependent method
		$tax_percentage = calc_tax_percentage($this->tax_rate_id, $tax_status_id, $this->net_value);

		$settlement_discount = $payment_term->calcSettlementDiscount($this->net_value);

		$net_value = bcsub($this->net_value, $settlement_discount);

		//tax_value is the tax percentage of the net value
		return round(bcmul($net_value, $tax_percentage, 4), 2);

	}

	public function awaitingDespatchStatus()
	{
		return 'R';
	}

	public function cancelStatus()
	{
		return 'X';
	}

	public function despatchStatus()
	{
		return 'D';
	}

	public function invoicedStatus()
	{
		return 'I';
	}

	public function newStatus()
	{
		return 'N';
	}

	public function partDespatchStatus()
	{
		return 'P';
	}

	public function pickedStatus()
	{
		return 'S';
	}

	public function lineCancelled()
	{
		return ($this->status=='X');
	}

}

// end of SOrderLine.php
