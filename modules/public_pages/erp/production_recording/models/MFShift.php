<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MFShift extends DataObject
{

	protected $version = '$Revision: 1.8 $';
	
	protected $defaultDisplayFields = array('shift_ref'
											,'shift'
											,'shift_date'
											,'mf_dept'=>'Dept'
											,'mf_centre'=>'Centre'
											,'comment'
											);
	
	function __construct($tablename = 'mf_shifts')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField	= 'id';
		$this->orderby	= array('shift_date', 'shift', 'mf_dept');
		$this->orderdir	= array('DESC', 'DESC', 'ASC');
		
		$this->identifierField='shift|| \'- \' ||shift_date|| \': \' ||mf_dept|| \': \' ||mf_centre';
 		$this->validateUniquenessOf(array('shift', 'shift_date', 'mf_dept_id', 'mf_centre_id'));
 		
// Define relationships
 		$this->belongsTo('MFDept', 'mf_dept_id', 'mf_dept');
 		$this->belongsTo('MFCentre', 'mf_centre_id', 'mf_centre'); 
 		$this->hasMany('MFShiftOutput', 'shift_output', 'mf_shift_id');
		$this->hasMany('MFShiftDowntime', 'shift_downtime', 'mf_shift_id');

// Define field formats

// Define enumerated types
		$this->setEnum('shift',array( 'AM'=>'AM'
									 ,'PM'=>'PM'
									 ,'Nights'=>'Nights'));
 		
	}

	public function getAllDept ()
	{
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('production_recording', 'is', true));
		
		$mfdept = DataObjectFactory::Factory('MFDept');
		
		return $mfdept->getAll($cc);
	}
	
	public function getAllCentres ($mfdept_id)
	{
		$mfdept = DataObjectFactory::Factory('MFDept');
		
		$mfdept->load($mfdept_id);
		
		$sh = new SearchHandler(new MFCentreCollection, false);
		
		$sh->addConstraint(new Constraint('production_recording', 'is', true));
		
		$mfdept->addSearchHandler('centres', $sh);
		
		$mfcentres = array();
		
		foreach ($mfdept->centres as $centre)
		{
			$mfcentres[$centre->id] = $centre->getIdentifierValue();
		}
		return $mfcentres;
	}

 	public function getDowntimeCodes()
 	{
  		$centredowntimes = DataObjectFactory::Factory('MFCentreDowntimeCode');
  		
  		$cc = new ConstraintChain();
  		
  		$cc->add(new Constraint('mf_centre_id', '=', $this->mf_centre_id));
  		
  		$centredowntimes->identifierField = 'downtime_code';
 		
  		$centredowntimes->orderby = 'downtime_code';
 		
  		return $centredowntimes->getAll($cc, null, true);
 		
 	}
 	
 	public function getItems()
 	{
		
		$mfoperation = DataObjectFactory::Factory('MFOperation');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('mfcentre_id', '=', $this->mf_centre_id));
		$cc->add(new Constraint('obsolete_date', 'is', 'NULL'));
		
		$cc1 = new ConstraintChain();
		$cc1->add(new Constraint('end_date', '>', fix_date(date(DATE_FORMAT))));
		$cc1->add(new Constraint('end_date', 'is', 'NULL'), 'OR');
		
		$cc->add($cc1);
		
		$mfoperation->idField			= 'stitem_id';
		$mfoperation->identifierField	= 'stitem';
		
		$mfoperation->orderby			= 'stitem';
		
		return $mfoperation->getAll($cc, TRUE, TRUE);
		
 	}
 	
 	public function getWasteTypes()
 	{
  		$centrewastetypes = DataObjectFactory::Factory('MFCentreWasteType');
  		
  		$cc = new ConstraintChain();
  		
  		$cc->add(new Constraint('mf_centre_id', '=', $this->mf_centre_id));
  		
  		$centrewastetypes->identifierField='waste_type';

  		return $centrewastetypes->getAll($cc, null, true);
 		
 	}
 	
}

// End of MFShift
