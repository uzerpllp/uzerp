<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CurrencyFormatter implements FieldFormatter {
	
	protected $version = '$Revision: 1.8 $';
	
	private $currency, $decimal_places;
	
	function __construct($currency, $decimal_places = 2)
	{
		$this->currency			= $currency;
		$this->decimal_places	= $decimal_places;
	}

	function format($value)
	{
		
		if (empty($value))
		{
			return $value;
		}
		
		$currency = DataObjectFactory::Factory('Currency');
		$currency->load($this->currency);
		
		if ($currency)
		{
			return $currency->symbol . number_format($value, $this->decimal_places);
		}
		
		return number_format($value, $this->decimal_places);
		
	}

}

// end of CurrencyFormatter.php