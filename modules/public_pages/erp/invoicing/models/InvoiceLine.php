<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class InvoiceLine extends DataObject
{
	
	protected $version = '$Revision: 1.9 $';
	
	public static function makeLine($data, $do, &$errors = [])
	{
		
		
		//net value is unit-price * quantity
		if (!isset($data['tax_value']))
		{
			//tax  (in the UK at least) is dependent on the tax_rate of the item, and the tax status of the customer.
			//this function is a wrapper to a call to a config-dependent method
			$data['tax_percentage'] = calc_tax_percentage($data['tax_rate_id'], $data['tax_status_id'], $data['net_value']);
			$data['tax_value'] = round(bcmul($data['net_value'], $data['tax_percentage'], 4), 2);
			$data['tax_rate_percent'] = bcmul($data['tax_percentage'], 100) ;
		}
		else
		{
			$tax_rate = DataObjectFactory::Factory('TaxRate');
			$tax_rate->load($data['tax_rate_id']);
			$data['tax_rate_percent'] = $tax_rate->percentage;
		}
		
		//gross value is net + tax; use bcadd to format the data
		$data['tax_value'] = bcadd($data['tax_value'], 0);
		$data['gross_value'] = bcadd($data['net_value'], $data['tax_value']);
		
		//then convert to the base currency
		if ($data['rate']==1)
		{
			$data['base_net_value'] = $data['net_value'];
			$data['base_tax_value'] = $data['tax_value'];
			$data['base_gross_value'] = $data['gross_value'];
		}
		else
		{
			$data['base_net_value'] = round(bcdiv($data['net_value'], $data['rate'], 4), 2);
			$data['base_tax_value'] = round(bcdiv($data['tax_value'], $data['rate'], 4), 2);
			$data['base_gross_value'] = round(bcadd($data['base_tax_value'], $data['base_net_value']), 2);
		}
		
		//and to the twin-currency
		$data['twin_net_value'] = round(bcmul($data['base_net_value'], $data['twin_rate'], 4), 2);
		$data['twin_tax_value'] = round(bcmul($data['base_tax_value'], $data['twin_rate'], 4), 2);
		$data['twin_gross_value'] = round(bcadd($data['twin_tax_value'], $data['twin_net_value']), 2);

		return DataObject::Factory($data, $errors, $do);

	}

	public function getCentres()
	{
		$account = DataObjectFactory::Factory('GLAccount');
		
		$account->load($this->glaccount_id);
		
		return $account->getCentres();
	}
	
	public function makeGLTransactions(&$gl_data, &$errors = array())
	{
		// provide some alternatives to get a comment
		$gl_data['comment']			= (!is_null($this->description)?$this->description:$this->item_description);
		$gl_data['glaccount_id']	= $this->glaccount_id;
		$gl_data['glcentre_id']		= $this->glcentre_id;
		$gl_data['base_net_value']	= $this->base_net_value;
		$gl_data['twin_net_value']	= $this->twin_net_value;
		
	}
		
}

// End of InvoiceLine
