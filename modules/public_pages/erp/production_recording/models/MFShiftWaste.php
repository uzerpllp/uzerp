<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class MFShiftWaste extends DataObject
{

	protected $version = '$Revision: 1.3 $';
	
	protected $defaultDisplayFields = array('shift'
											,'waste_type'
											,'qty'
											);
	
	function __construct($tablename = 'mf_shift_waste')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField = 'id';

// Define relationships
 		$this->belongsTo('MFShift', 'mf_shift_id', 'shift');
 		$this->belongsTo('MFCentreWasteType', 'mf_centre_waste_type_id', 'waste_type');
 		$this->hasOne('MFShift', 'mf_shift_id', 'shift_detail');
 		$this->hasOne('MFCentreWasteType', 'mf_centre_waste_type_id', 'centre_waste');

// Define field formats

// Define enumerated types
 		
	}

	function getWasteUom ($centre_waste_type_id)
	{
		$centre_waste = DataObjectFactory::Factory('MFCentreWasteType');
		
		if ($this->isLoaded() && empty($centre_waste_type_id))
		{
			$centre_waste_type_id = $this->mf_centre_waste_type_id;
		}
		
		if (empty($centre_waste_type_id))
		{
			return '';
		}
		else
		{
			$centre_waste->load($centre_waste_type_id);
			return $centre_waste->waste_type_detail->uom_name;
		}
	}

}

// End of MFShiftWaste
