<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLPeriod extends DataObject
{

	protected $version = '$Revision: 1.14 $';
	
	protected $defaultDisplayFields = array('year'
											,'period'
											,'description'
											,'enddate'
											,'tax_period'
											,'closed'
											,'tax_period_closed'
											);
	
	function __construct($tablename = 'gl_periods')
	{
		parent::__construct($tablename);
		
		$this->orderby			= array('year', 'period');
		$this->orderdir			= array('DESC', 'DESC');
		$this->idField			= 'id';
		$this->identifierField	= "year || ' - period ' || period || '(' || description || ')'";	
		
		$this->validateUniquenessOf(array('year','period'));
	}

	function getPeriodStartDate ($period)
	{
		if (!$this->isLoaded())
		{
			$this->load($period);
		}
		
		$previous = DataObjectFactory::Factory('GLPeriod');
		
		$sh = new SearchHandler(new GLPeriodCollection());
		
		$sh->fields='max(enddate)';
		
		$sh->addConstraint(new Constraint('enddate', '<', $this->enddate));
		
		$sh->setOrderby('enddate','DESC');
		
		if ($previous->loadBy($sh))
		{
			return date(DATE_FORMAT,strtotime('+1 days',strtotime($previous->enddate)));
		}
		else
		{
			return false;
		}
		
	}

	function getMaximumPeriod ()
	{
//
//	Returns one row containing the 'maximum' period
//	
		$sh = new SearchHandler(new GLPeriodCollection());
		
		$sh->fields = '*';
		
		$sh->setOrderby('enddate','DESC');
		
		$this->loadBy($sh);
	}
	
	function getCurrentPeriod()
	{
//
//	Returns one row containing the 'current' period
//  i.e. the oldest period that is still open
//	
		$sh = new SearchHandler(new GLPeriodCollection($this),false);
		
		$sh->fields = '*';
		
		$sh->addConstraint(new Constraint('closed', 'is not', 'true'));
		
		$sh->setOrderby('enddate','ASC');
		
		$this->loadBy($sh);
	}
	
	static function getPeriod($date)
	{
//
//	Returns one row containing the period in which the supplied date falls
//  or, if the relevant period is already closed,
//  returns the first open period after the supplied date
//	
		$db = &DB::Instance();
		
		$query = "SELECT id, enddate, year, period, tax_period, tax_period_closed
					FROM gl_periods a
				   WHERE enddate = ( SELECT min(enddate) 
				                       FROM gl_periods z
				                      WHERE not z.closed 
                                        AND z.enddate >= '".$date
		                            ."' AND a.usercompanyid = z.usercompanyid)";

		$result= $db->GetRow($query);
		return $result;
	}

	public function loadPeriod($date)
	{
		// Loads the period in which the supplied date falls
		
		$subquery = "(SELECT min(enddate) 
				       FROM gl_periods z
				      WHERE z.enddate >= '".fix_date($date)
		            ."' AND z.usercompanyid = ".EGS_COMPANY_ID.")";
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('enddate', '=', $subquery));
		
		return $this->loadBy($cc);
		
	}
	
	/**
	 * Return tax period numbers for the specified year
	 * 
	 * @param string $year
	 * 
	 * @return array Returns array of tax period numbers
	 */
	public static function getTaxPeriods($year = null)
	{
		$db = DB::Instance();
		$qvars = [EGS_COMPANY_ID];

		if ($year !== null)
		{
			array_push($year, $qvars);
		}

		$query = 'SELECT DISTINCT tax_period
					FROM gl_periods
					WHERE tax_period != 0 AND usercompanyid = ?';

		if ($year)
		{
			$query .= ' AND year = ?';
		}

		$query .= ' ORDER BY tax_period ASC';

		return $db->GetCol($query, $qvars);
	}
	
	public static function getFuturePeriods($period, $year)
	{
		$cc1 = new ConstraintChain;
		
		$cc1->add(new Constraint('period', '>', $period));
		$cc1->add(new Constraint('year', '=', $year));
		
		$cc2 = new ConstraintChain;
		
		$cc2->add(new Constraint('year', '>', $year));
		
		$cc = new ConstraintChain;
		
		$cc->add($cc1);
		$cc->add($cc2,'OR');
		
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$glperiod->orderby	= array('year','period');
		$glperiod->orderdir	= array('ASC','ASC');
		
		return $glperiod->getAll($cc);
	}
	
	public static function getIdsForTaxPeriod($tax_period, $year)
	{
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('tax_period', '=', $tax_period));
		$cc->add(new Constraint('year', '=', $year));
		
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$periods = $glperiod->getAll($cc);
		
		if (!$periods || count($periods)==0)
		{
			return false;
		}
		
		return array_keys($periods);
	}
	
	public function getIdsYTD($period, $year)
	{
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('period', '<=', $period));
		$cc->add(new Constraint('year', '=', $year));
		
		return array_keys($this->getAll($cc));
	}
	
	public function getTaxPeriodEnd($tax_period, $year)
	{
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('tax_period', '=', $tax_period));
		$cc->add(new Constraint('year', '=', $year));
		
		$sh = new SearchHandler(new GLPeriodCollection($this), false);
		
		$sh->addConstraintChain($cc);
		
		$sh->setOrderBy('enddate', 'DESC');
		
		$this->loadBy($sh);
	}

	function getNewStartPeriod(&$errors = array())
	{
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		
		$cc = new ConstraintCHain();
		
		$cc->add(new Constraint('year', '=', $this->year+1));
		$cc->add(new Constraint('period', '=', 0));
		
		$result=$glperiod->loadBy($cc);
		
		if (!$result)
		{
			$data['year']				= $this->year+1;
			$data['period']				= 0;
			$data['description']		= 'B/f Balances';
			$data['enddate']			= un_fix_date($this->enddate);
			$data['closed']				= true;
			$data['tax_period']			= 0;
			$data['tax_period_closed']	= true;
			
			$glperiod = DataObject::Factory($data, $errors, 'GLPeriod');
			
			$result = false;
			
			if ($glperiod)
			{
				$result = $glperiod->save();
			}
		}
		
		if ($result)
		{
			return $glperiod;
		}
		else
		{
			$errors[] = 'Failed to create Start Year Period';
			return false;
		}
	}
	
}

// End of GLPeriod

