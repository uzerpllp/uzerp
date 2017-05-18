<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class periodHandling {

	protected $version='$Revision: 1.11 $';
	
	static function close ($periodid, &$errors)
	{
		$result = false;
		
		$errors = array();
		
		$period = DataObjectFactory::Factory('GLPeriod');
		
		$period->load($periodid);
		
		if ($period->isLoaded())
		{
// Check for any unposted journals for the period to be closed
			$journal_header = DataObjectFactory::Factory('GLTransactionHeader');
			
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint('glperiods_id', '=', $period->{$period->idField}));
			$cc->add(new Constraint('status', '=', $journal_header->newStatus()));
			$cc->add(new Constraint('type', '=', $journal_header->standardJournal()));
				
			if ($journal_header->getCount($cc)>0)
			{
				$errors[] = 'Cannot close period due to unposted journals';
				
				return FALSE;
			}

// Ensure all assets are depreciated for the the period
			assetHandling::depreciateAll($errors);
			
// Close the period if no errors 
			if (count($errors)===0)
			{
				$result = $period->update($period->id,'closed','true');
			}

			if ($result)
			{
// Rollup year-to-datebalances
				$periodendbalances = new GLPeriodEndBalanceCollection(DataObjectFactory::Factory('GLPeriodEndBalance'));
				if ($periodendbalances->create($period) === FALSE)
				{
					$errors[] = 'Error creating period end balances';
				}
			}
			else
			{
				$errors[] = 'Error closing period';
			}
			
// Create the new period for next year
			$newperiod = DataObjectFactory::Factory('GLPeriod');
			
			$nextyear = date(DATE_FORMAT, strtotime('+12 months', strtotime($period->enddate)));
			
			$newperiod->loadPeriod($nextyear);
			
			if ($result && count($errors)===0 && !$newperiod->isLoaded())
			{
				if (!self::createNew($period, $errors))
				{
					$errors[] = 'Error creating new period for next year';
				}
			}
			
// If closing last period of the year, do year end
			$glparams = DataObjectFactory::Factory('GLParams');
			
			if (!is_numeric($glparams->number_of_periods_in_year()))
			{
				$errors[] = 'GL Parameters No.of periods in year not defined';
			}
			
			if ($result && count($errors)===0 && $period->period==$glparams->number_of_periods_in_year())
			{
				self::yearEnd($period, $errors);
			}
			
			return $period;
			
		}
		else
		{
			$errors[] = 'Error loading period details';
			return false;
		}
	}

	static function createPeriods (&$errors)
	{
		$period = DataObjectFactory::Factory('GLPeriod');
		
		$period->getCurrentPeriod();
		
// Get the current period
		if (!$period->isLoaded())
		{
// If it doesn't exist, get the most recent period
			$period->getMaximumPeriod();
		}
		
		$glparams = DataObjectFactory::Factory('GLParams');
		
// If the loaded period is less than the number of periods in the year
// assume that we want to create periods for this year
		if ($period->period < $glparams->number_of_periods_in_year())
		{
			$startperiod = $period->period+1;
			$oldyear	 = $period->year-1;
		}
		else
		{
// otherwise we want to create periods for next year
			$startperiod = 1;
			$oldyear	 = $period->year;
		}
		
// Get all the periods for the latest existing year
		$currentperiods = new GLPeriodCollection(DataObjectFactory::Factory('GLPeriod'));
		
		$sh = new SearchHandler($currentperiods, false);
		
		$sh->addConstraint(new Constraint('period', '>=', $startperiod));
		$sh->addConstraint(new Constraint('year', '=', $oldyear));
		
		$currentperiods->load($sh);
		
		foreach ($currentperiods as $current)
		{
// create new periods 12 months on from the existing
			$result = self::createNew($current, $errors);
			if (!$result || count($errors)>0)
			{
				break;
			}
		}
		
	}

	static function createNew ($period, &$errors)
	{
		$data = array();
		
		$data['period']		 = $period->period;
		$data['year']		 = $period->year+1;
		
// NB: this assumes the accounting periods are calendar months
		$data['enddate']	 = date(DATE_FORMAT, strtotime('+1 year', strtotime('+1 day', strtotime($period->enddate)))-1);
		$data['tax_period']	 = $period->tax_period;
		$data['description'] = $period->description;
		
		$newperiod = DataObject::Factory($data, $errors, 'GLPeriod');
		
		if (count($errors)===0 && $newperiod)
		{
			return $newperiod->save();
		}
		else
		{
			return false;
		}
		
	}
	
	static function yearEnd ($period, &$errors)
	{
		assetHandling::yearEnd($errors);
		
		$period_ids = $period->getIdsYTD($period->period, $period->year);

		$startperiod = $period->getNewStartPeriod($errors);
		$result = $startperiod;

		$glbalance		= DataObjectFactory::Factory('GLBalance');
		$newbalances	= array();
		$balances		= new GLBalanceCollection($glbalance);
		
		$balances->getYearEndBalances($period_ids,'P');
			
		$glparam		= DataObjectFactory::Factory('GLParams');
		$placcount_id	= $glparam->retained_profits_account();
		$plcentre_id	= $glparam->balance_sheet_cost_centre();

		$plbalance = 0;
		
		foreach ($balances as $balance)
		{
			if (count($errors)>0)
			{
				$result = false;
				break;
			}
			
			$plbalance = $balance->value;
		}
		
		$balances = new GLBalanceCollection($glbalance);
		
		$balances->getYearEndBalances($period_ids,'B');
		
		foreach ($balances as $balance)
		{
			if (count($errors)>0)
			{
				$result = false;
				break;
			}
			
			$data['glperiods_id']	= $startperiod->id;
			$data['glaccount_id']	= $balance->glaccount_id;
			$data['glcentre_id']	= $balance->glcentre_id;
			$data['value']			= $balance->value;
			
			if ($balance->glaccount_id==$placcount_id
				&& $balance->glcentre_id==$plcentre_id)
			{
					$data['value']	+=$plbalance;
					$plbalance		= 0;
			}
			
			$newbalances[] = DataObject::Factory($data, $errors, $balance);
		}
		if ($result && count($newbalances)>0)
		{
			foreach ($newbalances as $balance)
			{
				if ($result)
				{
					$result = $balance->save();
				}
			}
		}
		
		if ($plbalance<>0)
		{
			$data['glperiods_id']	= $startperiod->id;
			$data['glaccount_id']	= $placcount_id;
			$data['glcentre_id']	= $plcentre_id;
			$data['value']			= $plbalance;
			
			$balance = DataObject::Factory($data, $errors, 'GLBalance');
			
			if ($balance)
			{
				if (!$balance->save())
				{
					$errors[] = 'Failed to create P+L balance';
				}
			}
		}
	}

}

// End of periodHandling
