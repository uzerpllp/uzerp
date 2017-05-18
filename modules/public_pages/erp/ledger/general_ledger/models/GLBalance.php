<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLBalance extends DataObject
{

	protected $version = '$Revision: 1.10 $';
	
	protected $defaultDisplayFields = array('account'		=> 'Account'
											,'centre'		=> 'Centre'
											,'periods'		=> 'Period'
//											,'value'		=> 'Value'
											,'debit'			=> 'Debit'
											,'credit'			=> 'Credit'
											,'glaccount_id'	=> 'glaccount_id'
											,'glcentre_id'	=> 'glcentre_id'
											,'glperiods_id'	=> 'glperiods_id');
		
	function __construct($tablename = 'gl_balances')
	{
// Register non-persistent attributes
		$this->setAdditional('credit', 'numeric');
		$this->setAdditional('debit', 'numeric');
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
		$this->orderby = array('centre','account');
		
// Define relationships
		$this->belongsTo('GLAccount', 'glaccount_id', 'account');
		$this->belongsTo('GLCentre', 'glcentre_id', 'centre');
		$this->belongsTo('GLPeriod', 'glperiods_id', 'periods'); 

// Define field formats		
		$this->getField('credit')->setFormatter(new NumericFormatter());
		$this->getField('debit')->setFormatter(new NumericFormatter());
		
// Define validation
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array('glaccount_id'=>'glaccount_id','glcentre_id'=>'glcentre_id')));
		$this->validateUniquenessOf(array('glperiods_id','glcentre_id', 'glaccount_id'));

	}

	function getCurrent ($glperiods_id)
	{

		$current = 0;
		
		$balance = DataObjectFactory::Factory('GLBalance');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('glaccount_id', '=', $this->glaccount_id));
		$cc->add(new Constraint('glcentre_id', '=', $this->glcentre_id));
		$cc->add(new Constraint('glperiods_id', '=', $glperiods_id));
		
		$balance->loadBy($cc);
		
		if ($balance)
		{
			$current = $balance->value;
		}
		
		return $current;
	}
	
	function getCurrentBudget ($glperiods_id)
	{
		$current_budget = 0;
		
		$budget = DataObjectFactory::Factory('GLBudget');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('glaccount_id', '=', $this->glaccount_id));
		$cc->add(new Constraint('glcentre_id', '=', $this->glcentre_id));
		$cc->add(new Constraint('glperiods_id', '=', $glperiods_id));
		
		$budget->loadBy($cc);
		
		if ($budget)
		{
			$current_budget = $budget->value;
		}
		
		return $current_budget;
	}
	
	function getYTDBudget ($glperiods_id)
	{
		#echo("GLBalence::getCurrent ".$glperiods_id);
		$ytd_budget = 0;
		
		$period = DataObjectFactory::Factory('GLPeriod');
		
		$periods = array();
		
		if ($period->load($glperiods_id))
		{
			$periods = $period->getIdsYTD($period->period, $period->year);
		}
		
		$budget = DataObjectFactory::Factory('GLBudget');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('glaccount_id', '=', $this->glaccount_id));
		$cc->add(new Constraint('glcentre_id', '=', $this->glcentre_id));
		
		$periodsYTD = '('.implode(',', $periods).')';
		
		$cc->add(new Constraint('glperiods_id', 'in', $periodsYTD));
					
		$budget->loadBy($cc);
		
		if ($budget)
		{
			$ytd_budget = $budget->value;
		}
		
		return $ytd_budget;
	}

	function getSum ($periods, $glaccount_id = '', $glcentre_id = '')
	{

		$cc = new ConstraintChain;

		// constrain glperiods_id by either a single, or an array of ids
		if(is_array($periods))
		{
			$cc->add(new Constraint('glperiods_id', 'in', '('.implode(',', $periods).')'));
		}
		else
		{
			$cc->add(new Constraint('glperiods_id', '=', $periods));
		}
		
		// constrain glaccount_id
		if($glaccount_id!='')
		{
			$cc->add(new Constraint('glaccount_id', '=', $glaccount_id));
		}		
		
		// constrain glcentre_id
		if($glcentre_id!='')
		{
			$cc->add(new Constraint('glcentre_id', '=', $glcentre_id));
		}
		
		return parent::getSum('value', $cc, 'gl_balances');
	
	}
	
	public function load ($clause, $override = FALSE, $return = FALSE)
	{
		parent::load($clause, $override, $return);
		
		if ($this->isLoaded())
		{
			if (!is_null($this->value))
			{
				if ($this->value<0)
				{
					$this->credit	= bcmul($this->value, -1);
				}
				else
				{
					$this->debit	= bcadd($this->value, 0);
				}
			}
		}
		
		return $this;
	}
	
}

// End of GLBalance
