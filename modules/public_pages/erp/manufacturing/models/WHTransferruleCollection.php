<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHTransferruleCollection extends DataObjectCollection {
	
	protected $version='$Revision: 1.8 $';
	
	public $field;
		
	function __construct($do='WHTransferrule', $tablename='wh_transfer_rules_overview')
	{
		parent::__construct($do, $tablename);
					
	}
	
	function getFromLocations($whaction_id)
	{
		
		$sh=new SearchHandler($this, false);
		$sh->addConstraint(new Constraint('whaction_id', '=', $whaction_id));
		$sh->setFields(array('from_whlocation_id','from_location'));
		
		$this->load($sh);
		return $this->getAssoc();
				
	}
		
	function getToLocations($whaction_id, $from_whlocation_id)
	{
		
		$sh=new SearchHandler($this, false);
		$sh->addConstraint(new Constraint('whaction_id', '=', $whaction_id));
		
		if (!is_array($from_whlocation_id))
		{
			$from_whlocation_id=array($from_whlocation_id);
		}
		
		if (count($from_whlocation_id)>0)
		{
			$sh->addConstraint(new Constraint('from_whlocation_id', '=', '('.implode(',', $from_whlocation_id).')'));
		}
		
		$sh->setFields(array('to_whlocation_id','to_location'));
		
		$this->load($sh);
		return $this->getAssoc();
			
	}
		
}

// End of WHTransferruleCollection
