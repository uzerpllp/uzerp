<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfshiftwastesController extends ProductionRecordingController
{

	protected $version = '$Revision: 1.3 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFShiftWaste');
		
		$this->uses($this->_templateobject);
		
	}

	public function _new()
	{

		parent::_new();
		
		$mfshiftwaste = $this->_uses[$this->modeltype];
		
		if ($mfshiftwaste->isLoaded())
		{
			$this->_data['mf_shift_outputs_id']=$mfshiftwaste->mf_shift_outputs_id;
			$this->_data['mf_centre_waste_type_id']=$mfshiftwaste->mf_centre_waste_type_id;
		}
		
		if (isset($this->_data['mf_shift_outputs_id']))
		{
			$mfshiftoutput = DataObjectFactory::Factory('MFShiftOutput');
			
			$mfshiftoutput->load($this->_data['mf_shift_outputs_id']);
			
			if ($mfshiftoutput->isLoaded()) {
				
				$mfshift = $mfshiftoutput->shift_detail;
				
				if ($mfshift->isLoaded())
				{
					$waste_types = $mfshift->getWasteTypes();
					$this->view->set('waste_types', $waste_types);
				}
			}
		}

		if (isset($_POST[$this->modeltype]['mf_centre_waste_type_id']))
		{
			$waste_type_id = $_POST[$this->modeltype]['mf_centre_waste_type_id'];
		}
		elseif (isset($this->_data['mf_centre_waste_type_id']))
		{
			$waste_type_id = $this->_data['mf_centre_waste_type_id'];
		}
		else
		{
			$waste_type_id = key($waste_types);
		}

		$this->view->set('uom', $this->getWasteUom($waste_type_id));
		
	}

	function getWasteUom($_mf_centre_waste_type_id = '')
	{
	// used by ajax to get the UoM
	
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['mf_centre_waste_type_id'])) { $_mf_centre_waste_type_id=$this->_data['mf_centre_waste_type_id']; }
		}
		
		$value = $this->_templateobject->getWasteUom($_mf_centre_waste_type_id);
			
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$value);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $value;
		}
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Shift Waste':$base), $action);
	}

}

// End of MfshiftwastesController
