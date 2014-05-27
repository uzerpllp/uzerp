<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

abstract class SPOrder extends DataObject
{ 
	
	protected $version = '$Revision: 1.18 $';
	
	private $unsaved_lines = array();

	public static function makeHeader($data, $do, &$errors)
	{
		
		if (strtotime(fix_date($data['order_date']))>strtotime(fix_date(date(DATE_FORMAT))))
		{
			$errors[] = 'Order Date cannot be in the future';
			return false;
		}
		
		if (!isset($data['id']) || $data['id']=='')
		{
		
//			$generator = new OrderNumberHandler();
			$generator = new UniqueNumberHandler(false, ($data['type']!='T'));
			$data['order_number'] = $generator->handle(DataObjectFactory::Factory($do));
			
			$data['status'] = 'N';
			
			$user = getCurrentUser();
			
			$data['raised_by'] = $user->username;
		}

		//determine the base currency
		$currency = DataObjectFactory::Factory('Currency');
		$currency->load($data['currency_id']);
		$data['rate'] = $currency->rate;
			
		//determine the twin currency
		$glparams = DataObjectFactory::Factory('GLParams');
		
		$twin_currency = DataObjectFactory::Factory('Currency');
		$twin_currency->load($glparams->base_currency());
		
		$data['twin_rate']			= $twin_currency->rate;
		$data['twin_currency_id']	= $twin_currency->id;

		return DataObject::Factory($data, $errors, $do);
	}

	public function save($debug = false)
	{
		$db=DB::Instance();
		$db->startTrans();

		$result = parent::save($debug);
		
		if($result===false)
		{
			$flash = Flash::Instance();
			$flash->addError('Error saving Order Header : '.$db->ErrorMsg());
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
	}
	
	public function getDeliveryAddress ()
	{
		$address = DataObjectFactory::Factory('Address');
		
		$address->load($this->del_address_id);
		
		return $address;
		
	}

	protected function getLineStatuses($_orderline, $_orderlines)
	{
		$linestatus = array();
		
		$statuses = $_orderline->getEnumOptions('status');
		
		foreach ($statuses as $key=>$name)
		{
			$linestatus['linecount']	= 0;
			$linestatus['count'][$key]	= 0;
			$linestatus['value'][$name]	= 0;
		}
		
		$sh = new SearchHandler($_orderlines, false);
		
		$sh->setFields(array('status', 'sum(net_value) as net_value', 'count(*) as count'));
		
		$sh->setGroupBy('status');
		$sh->setOrderBy('status');
		
		$sh->addConstraint(new Constraint('order_id', '=', $this->id));
		
		$_orderlines->load($sh);
		
		if ($_orderlines->count()>0)
		{
			foreach ($_orderlines as $status)
			{
				$linestatus['linecount']			 		+= $status->count;
				$linestatus['count'][$status->id]			 = $status->count;
				$linestatus['value'][$statuses[$status->id]] = $status->net_value;
			}
		}
		return $linestatus;
	}

	protected function getNextLineNumber ($_orderline)
	{
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('order_id', '=', $this->id));
		
		$max_line_number=$_orderline->getMax('line_number', $cc);
		
		if (empty($max_line_number))
		{
			$max_line_number = 0;
		}
		return $max_line_number + 1;

	}
	
	function getFormatted($name, $html = TRUE)
	{
		$value = parent::getFormatted($name, $html);
		
		if ($name=='order_number' && $this->type=='T')
		{
			return $this->type.$value;
		}
		
		return $value;
		
	}
	
}

// End of SPOrder
