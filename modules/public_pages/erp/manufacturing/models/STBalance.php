<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class STBalance extends DataObject
{

	protected $version = '$Revision: 1.22 $';
	
	protected $defaultDisplayFields = array('stitem'		=> 'Stock Item'
										   ,'whbin'			=> 'Bin'
										   ,'balance'		=> 'Balance'
										   ,'uom_name'		=> 'UoM'
										   ,'supply_demand'	=> 'Supply/Demand'
										   ,'whlocation_id'
										   );
	
	function __construct($tablename = 'st_balances')
	{
		parent::__construct($tablename);
		
		$this->idField = 'id';
		
		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation');
		$this->belongsTo('WHBin', 'whbin_id', 'whbin');
		$this->belongsTo('STItem', 'stitem_id', 'stitem'); 

		$this->hasOne('STItem', 'stitem_id', 'stock_item');
	}

	public function getBalance()
	{
		return $this->balance;
	}

	public static function getBalances($field)
	{
		$balance = DataObjectFactory::Factory('STBalance');
		
		return $balance->getSum('balance', $field, 'st_balancesoverview');
	}

	public function save(&$errors)
	{
		if ($this->balance < 0)
		{
			$errors[] = 'You do not have sufficient balance of '.$this->stitem.' at '.$this->whlocation;
		}
		else
		{
			parent::save();
		}
	}
        
	static function getStockList($whlocation_id='')
	{
		//Get the obsolete items
		$stitems = new STItem();
		$sc = new ConstraintChain;
		$sc->add(new Constraint('obsolete_date', 'is not', 'NULL'));
		$obs_items = $stitems->getAll($sc);
		$obs_ids = array_keys($obs_items);
		$obsolete_ids = '(' . implode('),(', $obs_ids) . ')';
		
		$stbalance = DataObjectFactory::Factory('STBalance');
		
		$cc = new ConstraintChain();
		
		if (!is_array($whlocation_id))
		{
			$whlocation_id = array($whlocation_id);
		}
		
		if ( count($whlocation_id)>0 )
		{
			$cc->add(new Constraint('whlocation_id', 'in', '('.implode(',', $whlocation_id).')'));
		}

		if (count($obsolete_ids) > 0) {
			$cc->add(new Constraint('stitem_id','<> ANY', "(VALUES {$obsolete_ids})"));
		}
		
		$cc->add(new Constraint('balance', '>', 0));
		
		$stbalance->idField			= 'stitem_id';
		$stbalance->identifierField	= 'stitem';
		
		$stbalance->orderby = 'stitem';
		
		return $stbalance->getAll($cc, false, true);
	}

	static function getBinList($stitem_id, $whlocation_id)
	{
		// Replace with getBinListNew below
		$db = &DB::Instance();
		
		$criteria = " WHERE balance>0" ;
		
		if (!is_array($stitem_id))
		{
			$stitem_id = array($stitem_id);
		}
		
		if (!is_array($whlocation_id))
		{
			$whlocation_id = array($whlocation_id);
		}
		
		$criteria .= " AND whlocation_id IN (".implode(',', $whlocation_id).')';
		
		$criteria .= " AND stitem_id IN (".implode(',', $stitem_id).')';
		
		$query = "SELECT DISTINCT whbin_id
							  , whbin
				  FROM st_balancesoverview"
					.$criteria;
		
		return $db->GetAssoc($query);
		
	}
	
	function getBinListNew($_stitem_id, $_whlocation_id)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('balance', '>', 0));
		
		if (!is_array($_stitem_id))
		{
			$cc->add(new Constraint('stitem_id', '=', $_stitem_id));
		}
		else
		{
			$cc->add(new Constraint('stitem_id', 'in', '('.implode(',', $_stitem_id).')'));
		}
		
		if (!is_array($_whlocation_id))
		{
			$cc->add(new Constraint('whlocation_id', '=', $_whlocation_id));
		}
		else
		{
			$cc->add(new Constraint('whlocation_id', 'in', '('.implode(',', $_whlocation_id).')'));
		}
		
		$this->idField			= 'whbin_id';
		$this->identifierField	= 'whbin';
		
		return $this->getAll($cc, TRUE, TRUE);
		
	}
	
	public function getLocationList($_stitem_id, $_cc = '')
	{

		$cc = new ConstraintChain();
		
		if (!is_array($_stitem_id))
		{
			$cc->add(new Constraint('stitem_id', '=', $_stitem_id));
		}
		else
		{
			$cc->add(new Constraint('stitem_id', 'in', '('.implode(',', $_stitem_id).')'));
		}
		
		if ($_cc instanceof ConstraintChain)
		{
			$cc->add($_cc);
		}
		
		$this->idField			= 'whlocation_id';
		$this->identifierField	= 'whlocation';
		
		return $this->getAll($cc, TRUE, TRUE);
		
	}
	
	public static function getTotalBalance()
	{
		$db = DB::Instance();
		
		// Change this to use parent getSum
		$query = "SELECT SUM(balance)
			FROM st_balances
			WHERE usercompanyid = ".EGS_COMPANY_ID;
		
		return $db->GetOne($query);
	}
	
	public static function getTotalValuation()
	{
		$db = DB::Instance();
		
		// Change this to use parent getSum
		$query = "SELECT SUM(valuation)
			FROM st_balancesoverview
			WHERE usercompanyid = ".EGS_COMPANY_ID;
		
		return $db->GetOne($query);
	}
	
	public function getUoM ()
	{
		$stitem = DataObjectFactory::Factory('STItem');
		
		$stitem->load($this->stitem_id);
		
		if ($stitem)
		{
			return $stitem->uom_name;
		}
		else
		{
			return '';
		}
		
	}
	
	public function getStockBalance ($stitem, $locations=null)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('stitem_id', '=', $stitem));
		
		if (!empty($locations))
		{
			if (is_array($locations))
			{
				$cc->add(new Constraint('whlocation_id', 'in', '('.implode(',', $locations).')'));
			}
			else
			{
				$cc->add(new Constraint('whlocation_id', '=', $locations));
			}
		}
		
		return $this->getSum('balance', $cc);
	}
}

// End of STBalance
