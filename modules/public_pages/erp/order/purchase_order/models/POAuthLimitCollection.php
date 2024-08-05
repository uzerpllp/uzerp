<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAuthLimitCollection extends DataObjectCollection
{
	protected $view;
	
	public $field;

	function __construct($do = 'POAuthLimit', $tablename = 'po_authlimitsoverview')
	{
		parent::__construct($do, $tablename);
		
		$this->orderby	= array('username', 'cost_centre');
		
		$this->view		= '';

	}

	function getAuthList($account = '', $centre = '', $value = '')
	{
		$this->_tablename = "po_authlist";

		$sh = new SearchHandler($this, false);

		if (!empty($account))
		{
			$sh->addConstraint(new Constraint('glaccount_id', '=', $account));
		}

		if (!empty($centre))
		{
			$sh->addConstraint(new Constraint('glcentre_id', '=', $centre));
		}

		if (!empty($value))
		{
			$sh->addConstraint(new Constraint('order_limit', '>=', $value));
		}
		
		$sh->setOrderBy('username');
		$this->load($sh);
	}
		
}

// End of POAuthLimitCollection
