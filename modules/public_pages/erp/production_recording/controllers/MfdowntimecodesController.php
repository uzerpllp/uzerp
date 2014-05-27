<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfdowntimecodesController extends ProductionRecordingController
{

	protected $version = '$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFDowntimeCode');
		
		$this->uses($this->_templateobject);
		
	}

	public function index()
	{

		$this->view->set('clickaction', 'view');
		
		parent::index(new DataObjectCollection($this->_templateobject));
				
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'Add Downtime Code'
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

		$mfdowntimecode = $this->_uses[$this->modeltype];
		
		$this->view->set('mfdowntimecode', $mfdowntimecode);
		
		$this->view->set('mf_centres', $this->getCentres());
		
		if ($mfdowntimecode->isLoaded())
		{
			$this->view->set('selected_centres', $mfdowntimecode->mf_centres->getAssoc());
		}
		
		$this->view->set('mfcentredowntimecode', DataObjectFactory::Factory('MFCentreDowntimeCode'));
		
	}
	
	public function save()
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		
		if (!empty($this->_data['MFDowntimeCode']['id']))
		{
			$mfdowntimecode = $this->_uses[$this->modeltype];
			$mfdowntimecode->load($this->_data['MFDowntimeCode']['id']);
			
			if ($mfdowntimecode->isLoaded())
			{
				foreach ($mfdowntimecode->mf_centres as $mfcentredowntimecode)
				{
					$mfcentredowntimecode->delete();
				}
			}
		}
		
		if (isset($this->_data['MFCentreDowntimeCode']))
		{
			$mfcentredowntimecode = DataObjectCollection::joinarray($this->_data['MFCentreDowntimeCode']);
			unset($this->_data['MFCentreDowntimeCode']);
			
			foreach ($mfcentredowntimecode as $key=>$data)
			{
				$this->_data[$key]['MFCentreDowntimeCode']=$data;
			}
		}
		
		parent::save();
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Downtime Codes':$base), $action);
	}

}

// End of MfdowntimecodesController
