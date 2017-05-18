<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfwastetypesController extends ProductionRecordingController
{

	protected $version = '$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFWasteType');
		
		$this->uses($this->_templateobject);
		
	}

	public function index()
	{

		$this->view->set('clickaction', 'view');
		
		parent::index(new DataObjectCollection($this->_templateobject, 'mf_waste_types_overview'));
				
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'Add Waste Type'
							,'link'=>array('modules'=>$this->_modules
										  ,'controller'=>$this->name
										  ,'action'=>'new'
										  )
							)
				 )
			);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}

	public function _new()
	{
		
		parent::_new();

		$mfwastetype = $this->_uses[$this->modeltype];
		
		$this->view->set('mfwastetype', $mfwastetype);
		
		$this->view->set('mf_centres', $this->getCentres());
		
		if ($mfwastetype->isLoaded())
		{
			$this->view->set('selected_centres', $mfwastetype->mf_centres->getAssoc());
		}
		
		$this->view->set('mfcentrewastetype', DataObjectFactory::Factory('MFCentreWasteType'));
	}
	
	public function save()
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		
		if (!empty($this->_data['MFWasteType']['id']))
		{
			
			$mfwastetype = $this->_uses[$this->modeltype];
			
			$mfwastetype->load($this->_data['MFWasteType']['id']);
			
			if ($mfwastetype->isLoaded())
			{
				foreach ($mfwastetype->mf_centres as $mfcentrewastetype)
				{
					$mfcentrewastetype->delete();
				}
			}
		}

		if (isset($this->_data['MFCentreWasteType']))
		{
			$mfcentrewastetype = DataObjectCollection::joinarray($this->_data['MFCentreWasteType']);
			
			unset($this->_data['MFCentreWasteType']);
			
			foreach ($mfcentrewastetype as $key=>$data)
			{
				$this->_data[$key]['MFCentreWasteType']=$data;
			}
		}
		
		parent::save();
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Waste Types':$base), $action);
	}

}

// End of MfwastetypesController
