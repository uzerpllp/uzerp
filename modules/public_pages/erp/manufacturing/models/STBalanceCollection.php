<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class STBalanceCollection extends DataObjectCollection
{
	
	protected $version='$Revision: 1.9 $';
	public $field;
	
	function __construct($do='STBalance', $tablename='st_balancesoverview')
	{
		parent::__construct($do, $tablename);
		
	}

	public function getLocationList($stitem_id, $cc='')
	{

		// Move this to STBalance as getAll - more efficient!
		$sh=new SearchHandler($this, false);
		
		if (!is_array($stitem_id))
		{
			$sh->addConstraint(new Constraint('stitem_id', '=', $stitem_id));
		}
		else
		{
			$sh->addConstraint(new Constraint('stitem_id', 'in', '('.implode(',', $stitem_id).')'));
		}
		
		if ($cc instanceof ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$sh->setFields(array('whlocation_id', 'whlocation'));
		
		// Return rows to loop round array
		$rows = $this->load($sh, '', RETURN_ROWS);
		
		$list = array();
		
		if (count($rows) > 0)
		{
			foreach ($rows as $row)
			{
				$list[$row['id']] = $row['whlocation'];
			}
		}
			
		return $list;
	}
	
}

// End of STBalanceCollection
