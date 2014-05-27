<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHLocation extends DataObject
{

	protected $version = '$Revision: 1.14 $';
	
	protected $defaultDisplayFields = array('location'
											,'description'
											,'has_balance'
											,'bin_controlled'
											,'saleable'
											,'supply_demand'
											,'pickable'
											,'whstore_id'
											,'glaccount_id'
											,'glcentre_id');
	
	function __construct($tablename = 'wh_locations')
	{
// Register non-persistent attributes
		
// Construct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= "location ||'-'|| description";		
		$this->orderby			= 'location';
		
// Define validation
		$this->validateUniquenessOf(array('whstore_id','location'));
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array('glaccount_id'=>'glaccount_id', 'glcentre_id'=>'glcentre_id')));
 		
// Define relationships
 		$this->belongsTo('WHStore', 'whstore_id', 'whstore'); 
 		$this->belongsTo('GLAccount', 'glaccount_id', 'glaccount'); 
 		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
 		$this->hasMany('WHBin', 'bins', 'whlocation_id');
 		$this->hasMany('STBalance', 'balances', 'whlocation_id');
 		
// Define field formats

// Define enumerated types

	}

	public function haveBalances($whlocation_ids)
	{
// Returns true if all locations have balances
		
		if (!is_array($whlocation_ids))
		{
			$whlocation_ids = array($whlocation_ids);
		}
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('has_balance', 'IS NOT', true));
		
		if ( count($whlocation_ids)>0 )
		{
			$cc->add(new Constraint('id', 'in', '('.implode(',', $whlocation_ids).')'));
		}
		
		$result = $this->getAll($cc);
		
		if (count($result)>0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	public function isBalanceEnabled()
	{
		return ($this->has_balance==='t');
	}

	public function isBinControlled()
	{
		return ($this->bin_controlled==='t');
	}

	static function getStoreLocation($id)
	{
		
		$location = DataObjectFactory::Factory('WHLocation');
		
		$location->load($id);
		
		if ($location->isLoaded())
		{
			return $location->whstore;
		}
		else
		{
			return '';
		}
		
	}
	
	static function getSaleLocations($whstore = "")
	{
		
		$location = DataObjectFactory::Factory('WHLocation');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('saleable', 'is', true));
		
		if (!empty($whstore))
		{
			$cc->add(new Constraint('whstore_id', '=', $whstore));
		}
		
		$locations=$location->getAll($cc);
		
		return array_keys($locations);
		
	}
	
	public function getBinList()
	{
		$binlist = array();
		
		foreach ($this->bins as $bin) 
		{
			$binlist[$bin->id] = $bin->getIdentifierValue();
		}
		
		return $binlist;
	}

	public function total_valuation()
	{
		$total = 0;
		
		foreach ($this->balances as $balance)
		{
			$total += $balance->valuation;
		}
		
		return $total;
	}
	
	public function total_balance()
	{
		$total = 0;
		
		foreach ($this->balances as $balance)
		{
			$total += $balance->balance;
		}
		
		return $total;
	}
	
}
?>