<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class WHTransferrule extends DataObject {

	protected $version='$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('whaction_id'
										   ,'from_whlocation_id'
										   ,'to_whlocation_id'
										   ,'from_location'
										   ,'to_location'
										   ,'action_name');

	function __construct($tablename='wh_transfer_rules') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby='action_name, from_location, to_location';
		
		$this->belongsTo('WHLocation', 'from_whlocation_id', 'from_location');
		$this->belongsTo('WHLocation', 'to_whlocation_id', 'to_location');
		$this->belongsTo('WHAction', 'whaction_id', 'action_name');
		$this->identifierField="action_name";

	}

	static function getTransferId() {
		$db=&DB::Instance();
		$query="SELECT NEXTVAL('wh_transfer_id_seq') as transfer_id";
		$result=$db->Execute($query);
		return $result->fetchObj();
	}

	function getFromLocations($whaction_id)
	{
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('whaction_id', '=', $whaction_id));
		$this->idField = 'from_whlocation_id';
		$this->identifierField = 'from_location';
		
		return $this->getAll($cc, FALSE, TRUE);
		
	}
		
	function getToLocations($whaction_id, $from_whlocation_id)
	{
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('whaction_id', '=', $whaction_id));
		
		if (!is_array($from_whlocation_id))
		{
			$from_whlocation_id=array($from_whlocation_id);
		}
		
		if (count($from_whlocation_id)>0)
		{
			$cc->add(new Constraint('from_whlocation_id', 'in', '('.implode(',', $from_whlocation_id).')'));
		}
		
		$this->idField = 'to_whlocation_id';
		$this->identifierField = 'to_location';
		
		return $this->getAll($cc, FALSE, TRUE);
	
	}
		
}
?>