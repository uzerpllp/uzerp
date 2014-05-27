<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Asset extends DataObject
{

	protected $version = '$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array(
		'code',
		'description',
		'serial_no',
		'argroup'			=> 'Asset Group',
		'arlocation'		=> 'Asset Location',
		'aranalysis'		=> 'Asset Analysis',
		'supplier',
		'purchase_date',
		'purchase_price',
		'wd_value'			=> 'Written Down Value',
		'residual_value',
		'disposal_date'
	);
	
	function __construct($tablename = 'ar_master')
	{
		
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'code';
		$this->orderby			= 'code';
		
// Define validation
		$this->validateUniquenessOf('code'); 

// Define relationships
		$this->belongsTo('ARAnalysis', 'aranalysis_id', 'aranalysis'); 
 		$this->belongsTo('ARGroup', 'argroup_id', 'argroup'); 
  		$this->belongsTo('ARLocation', 'arlocation_id', 'arlocation'); 
  		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier'); 

// Define field formats		
  		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
 		$this->getField('residual_value')->setFormatter(new CurrencyFormatter($base_currency));
		$this->getField('purchase_price')->setFormatter(new CurrencyFormatter($base_currency));
  		$this->getField('bfwd_value')->setFormatter(new CurrencyFormatter($base_currency));
  		$this->getField('ty_depn')->setFormatter(new CurrencyFormatter($base_currency));
  		$this->getField('td_depn')->setFormatter(new CurrencyFormatter($base_currency));
  		$this->getField('wd_value')->setFormatter(new CurrencyFormatter($base_currency));
 		
// Define enumerated types
	
	}

	function depreciation (&$errors = array())
	{
		
		$group = DataObjectFactory::Factory('ARGroup');
		$group->load($this->argroup_id);
		
		if (!$group)
		{
			$errors[] = 'Error getting Asset Group details';
			return FALSE;
		}
		
		$currentperiod = DataObjectFactory::Factory('GLPeriod');
		$currentperiod->getCurrentPeriod();
		
		if (!$currentperiod)
		{
			$errors[] = 'Error getting Current Period details';
			return FALSE;
		}
		
		$purchaseperiod = DataObjectFactory::Factory('GLPeriod');
		$purchaseperiod->load($this->purchase_period_id);
		
		if (!$purchaseperiod)
		{
			$errors[] = 'Error getting Purchase Period details';
			return FALSE;
		}
		
		if ($currentperiod->enddate < $purchaseperiod->enddate)
		{
			// Current period is before asset purchase period so no depreciation to calculate
			return 0;
		}
		
		switch ($group->depn_method)
		{
			
			case 'E':
				return $this->economicLife($group, $currentperiod, $purchaseperiod, $errors);
				break;
				
			case 'P':
				return $this->percentage($group, $currentperiod, $purchaseperiod, $errors);
				break;
				
			case 'R':
				return $this->reducingBalance($group, $currentperiod, $purchaseperiod, $errors);
				break;
				
			case 'S':
				return $this->straightLine($group, $currentperiod, $purchaseperiod, $errors);
				break;
				
		}
		
	}
	
	protected function straightLine($group, $currentperiod, $purchaseperiod, &$errors)
	{
		
		//		if ($group->depn_first_year=='t' || ($currentperiod->year>$purchaseperiod->year)) {
		//			$depnperiod=$currentperiod->period;
		//		} else {
		//			$depnperiod=$currentperiod->period-$purchaseperiod->period+1;
		//		}
		
		$params = DataObjectFactory::Factory('GLParams');
		
		if ($params->number_of_periods_in_year() > 0)
		{
			
			if ($group->depn_first_year == 't')
			{
				$stperiod = 1;
			}
			else
			{
				$stperiod = $purchaseperiod->period;
			}
			
			$depnperiod = $currentperiod->period + ($params->number_of_periods_in_year() - $stperiod + 1) + $params->number_of_periods_in_year() * ($currentperiod->year - $purchaseperiod->year - 1);
			
//			$value=($this->bfwd_value>0)?$this->bfwd_value:$this->purchase_price;
			$percent = ($group->depn_term>0)?(100/$group->depn_term):$group->depn_percent;
		
//			return round($value*($percent/100)*($depnperiod/$params->number_of_periods_in_year()),2);
			$depn_td = round($this->purchase_price * ($percent / 100) * ($depnperiod / $params->number_of_periods_in_year()), 2);
			
			if ($depn_td > $this->purchase_price)
			{
				return $this->purchase_price;
			}
			else
			{
				return bcadd($depn_td, 0);
			}
			
		}
		else
		{
			// Unable to calculate depreciation due to an error
			$errors[] = 'GLParams - No. of periods in year - not set';
			return FALSE;
		}
		
	}
	
	protected function reducingBalance($group, $currentperiod, $purchaseperiod, &$errors)
	{
		
		$depnperiod = $currentperiod->period;
		
		if ($currentperiod->year == $purchaseperiod->year)
		{
			
			$percent = $group->depn_percent_yr1;
			
			if ($group->depn_first_year === 'f')
			{
				$depnperiod = $currentperiod->period-$purchaseperiod->period+1;
			}
			
		}
		else
		{
			$percent = $group->depn_percent;
		}

		$params	= DataObjectFactory::Factory('GLParams');
		$value	= ($this->bfwd_value>0)?$this->bfwd_value:$this->purchase_price;
		
		if ($this->bfwd_value > 0)
		{
			$depn_previousyear = $this->purchase_price - $this->bfwd_value;
		}
		else
		{
			$depn_previousyear = 0;
		}
		
		if ($params->number_of_periods_in_year() > 0)
		{
			return bcadd($depn_previousyear + round($value * ($percent / 100) * ($depnperiod / $params->number_of_periods_in_year()), 2), 0);
		}
		else
		{
			$errors[] = 'GLParams - No. of periods in year - not set';
		}
		
		// Unable to calculate depreciation due to an error
		return FALSE;

	}
	
	protected function percentage($group, $currentperiod, $purchaseperiod, &$errors)
	{

		$depnperiod = $currentperiod->period;
		
		if ($currentperiod->year == $purchaseperiod->year)
		{
			
			$percent = $group->depn_percent_yr1;
			
			if ($group->depn_first_year === 'f')
			{
				$depnperiod = $currentperiod->period - $purchaseperiod->period + 1;
			}
			
		}
		else
		{
			$percent = $group->depn_percent;
		}

		$params	= DataObjectFactory::Factory('GLParams');
		$value	= ($this->bfwd_value>0)?$this->bfwd_value:$this->purchase_price;
		
		if ($this->bfwd_value > 0)
		{
			$depn_previousyear = $this->purchase_price - $this->bfwd_value;
		}
		else
		{
			$depn_previousyear = 0;
		}
		
		if ($params->number_of_periods_in_year() > 0)
		{
			return bcadd($depn_previousyear + round($value * ($percent / 100) * ($depnperiod / $params->number_of_periods_in_year()), 2), 0);
		}
		else
		{
			$errors[] = 'GLParams - No. of periods in year - not set';
		}
		
		// Unable to calculate depreciation due to an error
		return FALSE;

	}
	
	protected function economicLife($group, $currentperiod, $purchaseperiod, &$errors)
	{
		
	}
	
}

// end of Asset.php
