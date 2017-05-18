<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAwaitingAuthCollection extends DataObjectCollection
{
	
	protected $version = '$Revision: 1.5 $';
	
	public $field;

	function __construct($do = 'POAwaitingAuth')
	{
		parent::__construct($do);
	
	}

	function deleteAll()
	{
		foreach ($this as $rec)
		{
			$rec->delete();	
		}
	}
	
	function loadBy ($username = '', $order = '')
	{
		$db = DB::Instance();
		
		$sh = new SearchHandler($this, false);
		
		if (!empty($username))
		{
			$sh->addConstraint(new Constraint('username', '=', $username));
		}
		
		if (!empty($order))
		{
			$sh->addConstraint(new Constraint('order_id', '=', $order));
		}
		
		$this->load($sh);
	}
	
	function getOrderList ($username = '')
	{
		$this->loadBy($username);
		
		$authlist = array();
		
		foreach ($this as $authority)
		{
			$authlist[] = $authority->order_id;
		}
		
		return implode(',', $authlist);
	}
	
}

// End of POAwaitingAuthCollection
