<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SPOrderLine extends DataObject
{
	
	protected $version = '$Revision: 1.5 $';
	
	public function productUom()
	{
		$product = self::newProductline();
		
		return 'Product Line is '.$this->productline_id.' Product UoM is '.$product->stuom_id.' - '.$product->uom_name;
	}

	public function getUomList()
	{
		$uom_list = array();
		
		$product = self::newProductline();
		
		if ($this->productline_id && $product && $product->stuom_id)
		{
			$uom_list[$product->stuom_id] = $product->uom_name;
		}
		else
		{
			$uom = DataObjectFactory::Factory('STuom');
			
			$stitem = DataObjectFactory::Factory('STItem');
			
			if ($stitem->load($this->stitem_id))
			{
			// Get UoM list for Stock Conversions 
				$uom_temp_list = STuomconversion::getUomList($stitem->id, $stitem->uom_id);
				
				if (count($uom_temp_list)==0)
				{
				// Get UoM list for world conversions
					$uom_temp_list=SYuomconversion::getUomList($stitem->uom_id);
				}
				
				$uom->load($stitem->uom_id);
				
				// Get the UoM of the Stock Item
				$uom_list[$stitem->uom_id] = $uom->getUomName();
				
				if (count($uom_temp_list)>0)
				{
					$uom_list += $uom_temp_list;
				}
			}
			else
			{
			// No stock item so just get the list of UoMs
				$uom_list = $uom->getAll();
			}
		}
		
		return $uom_list;
	}

	public function getGLAccounts()
	{
		$accounts_list = array();
		
		$product = self::newProductline();
		
		if ($this->productline_id && $product)
		{
			$accounts_list[$product->glaccount_id] = $product->glaccount;
		}
		else
		{
			$glaccount = DataObjectFactory::Factory('GLAccount');
			$accounts_list = $glaccount->getAll();
			asort($accounts_list, SORT_NUMERIC);
		}
		
		return $accounts_list;
	}

	public function getGLCentres()
	{
		$centres_list = array();
		$product = self::newProductline();
		
		if ($this->productline_id && $product)
		{
			$centres_list[$product->glcentre_id] = $product->glcentre;
		}
		else
		{
			$glaccount = DataObjectFactory::Factory('GLAccount');
			
			if ($glaccount->load($this->glaccount_id))
			{
				$centres_list = $glaccount->getCentres();
				asort($centres_list, SORT_NUMERIC);
			}
			else
			{
				$centres_list[-1] = 'None';
			}
		}
		
		return $centres_list;
	}

	public function getTaxRates()
	{
		$tax_rates_list = array();
		
		$product = self::newProductLine();
		
		if ($this->productline_id && $product)
		{
			$tax_rates_list[$product->tax_rate_id] = $product->taxrate;
		}
		else
		{
			$tax_rates = DataObjectFactory::Factory('TaxRate');
			$tax_rates_list = $tax_rates->getAll();
			ksort($tax_rates_list, SORT_NUMERIC);
		}
		
		return $tax_rates_list;
	}

	private function newProductLine()
	{
		$class = get_class($this);
		
		switch ($class)
		{
			case 'POrderLine':
				$product = DataObjectFactory::Factory('POProductline');
				break;
			case 'SOrderLine':
				$product = DataObjectFactory::Factory('SOProductline');
				break;
			default:
				$product = false;
		}
		
		if ($product)
		{
			$product->load($this->productline_id);
		}
		
		return $product;
	}
}

// End of SPOrderLine
