<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFShiftOutput extends DataObject
{

	protected $version = '$Revision: 1.9 $';
	
	protected $defaultDisplayFields = array('output'
											,'uom_name'
											,'stitem'=>'Stock Item'
											,'planned_time'
											,'run_time_speed'
											,'operators'
											,'wo_number'
											,'stitem_id'
											,'uom_id'
											,'work_order_id'
											);
	
	function __construct($tablename = 'mf_shift_outputs')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';
 		
// Define relationships
 		$this->belongsTo('MFShift', 'mf_shift_id', 'shift');
 		$this->hasOne('MFShift', 'mf_shift_id', 'shift_detail');
 		$this->belongsTo('STItem', 'stitem_id', 'stitem');
 		$this->belongsTo('STUom', 'uom_id', 'uom_name');
 		$this->belongsTo('MFWorkorder', 'work_order_id', 'wo_number');
 		$this->hasMany('MFShiftWaste', 'shift_waste', 'mf_shift_outputs_id');
 		
// Define field formats

// Define validation
		$this->getField('planned_time')->addValidator(new ValueValidator(0, '>'));
		$this->getField('run_time_speed')->addValidator(new ValueValidator(0, '>'));
		
// Define enumerated types
 		
	}

	function getUomList ($stitem_id)
	{
		$stitem = DataObjectFactory::Factory('STItem');
		
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id = $this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			return '';
		}
		else
		{
			$stitem->load($stitem_id);
			return $stitem->getUomList();
		}
		
	}

 	public function getWorkOrders($stitem_id='')
 	{
  		
 		if (empty($stitem_id))
 		{
  			return array();
  		}
		
 		$mfworkorders = new MFWorkorderCollection();
		
 		$sh = new SearchHandler($mfworkorders, false);
		
  		$sh->addConstraint(new Constraint('stitem_id', '=', $stitem_id));
		
  		$sh->setOrderby('wo_number', 'DESC');
		
  		$mfworkorders->load($sh);
		
		return $mfworkorders->getAssoc('wo_number');
		
 	}
	
}

// End of MFShiftOutput
