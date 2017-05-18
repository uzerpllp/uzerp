<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfshiftdowntimesController extends ProductionRecordingController {

	protected $version = '$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null) {
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFShiftDowntime');
		
		$this->uses($this->_templateobject);
		
	}

	public function _new()
	{

		parent::_new();
		
		$mfshiftdowntime = $this->_uses[$this->modeltype];
		
		if ($mfshiftdowntime->isLoaded())
		{
			$this->_data['mf_shift_id']=$mfshiftdowntime->mf_shift_id;
		}
		
		if (isset($this->_data['mf_shift_id']))
		{
			$mfshift = DataObjectFactory::Factory('MFShift');
			
			$mfshift->load($this->_data['mf_shift_id']);
			
			if ($mfshift->isLoaded())
			{
				$this->view->set('downtime_codes', $mfshift->getDowntimeCodes());
			}
		}
		else
		{
			$this->dataError();
			sendBack();
		}

	}

	public function viewmfshift ()
	{
		
		if (!$this->CheckParams('mf_shift_id'))
		{
			sendBack();
		}
		
		$mfshift = DataObjectFactory::Factory('MFShift');
		
		$mfshift->load($this->_data['mf_shift_id']);
		
		if (!$mfshift->isLoaded())
		{
			$this->dataError();
			sendBack();
		}
		
		$this->view->set('model', $mfshift);
		
		$mfshiftdowntimes = new MFShiftDowntimeCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($mfshiftdowntimes);
		
		$sh->setFields(array('id', 'downtime_code', 'down_time', 'time_period'));
		
		$sh->addConstraint(new Constraint('mf_shift_id', '=', $this->_data['mf_shift_id']));
		
		parent::index($mfshiftdowntimes, $sh);
		
		$this->view->set('clickaction','edit');
		$this->view->set('page_title', $this->getPageName('', 'View'));
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Shift Downtime':$base), $action);
	}

}

// End of MfshiftdowntimesController
