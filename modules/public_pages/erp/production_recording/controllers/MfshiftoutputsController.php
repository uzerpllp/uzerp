<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfshiftoutputsController extends ProductionRecordingController
{

	protected $version = '$Revision: 1.10 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFShiftOutput');
		
		$this->uses($this->_templateobject);
		
	}

	public function _new()
	{
		
		$flash=Flash::Instance();
		
		// need to store the ajax flag in a different variable and the unset the original
		// this is to prevent any functions that are further called from returning the wrong datatype
		$ajax=isset($this->_data['ajax']);
		unset($this->_data['ajax']);
		
		parent::_new();
		
		$mfshiftoutput = $this->_uses[$this->modeltype];
	
		if ($mfshiftoutput->isLoaded())
		{
			$this->_data['mf_shift_id'] = $mfshiftoutput->mf_shift_id;
		}
		elseif (isset($this->_data[$this->modeltype]['mf_shift_id']))
		{
			$this->_data['mf_shift_id'] = $this->_data[$this->modeltype]['mf_shift_id'];
		}
		
		$stitems=array();
		
		if (isset($this->_data['mf_shift_id']))
		{
			$mfshift = DataObjectFactory::Factory('MFShift');
			
			$mfshift->load($this->_data['mf_shift_id']);
			
			if ($mfshift->isLoaded())
			{
				$this->view->set('mfshift', $mfshift);
				
				$stitems = $mfshift->getItems();
				
			}
			
			$mf_centre_id = $mfshift->mf_centre_id;
		}
		else
		{
			$this->dataError();
			sendBack();
		}
		
		$this->view->set('stitems', $stitems);

		if (isset($_POST[$this->modeltype]['stitem_id']))
		{
			$stitem_id = $_POST[$this->modeltype]['stitem_id'];
		}
		elseif ($mfshiftoutput->isLoaded())
		{
			$stitem_id = $mfshiftoutput->stitem_id;
		}
		elseif (isset($this->_data['stitem_id']))
		{
			$stitem_id = $this->_data['stitem_id'];
		}
		else
		{
			$stitem_id = key($stitems);
		}
		
		if (empty($stitem_id))
		{
			$flash->addError('There are no currently manufactured items for this centre');
			sendBack();
		}
		else
		{
			 
			$this->view->set('run_time_speed', $this->getRunTimeSpeed($stitem_id, $mf_centre_id));
			
			$this->view->set('work_orders', $this->getWorkordersList($stitem_id));
			
			$this->view->set('uoms', $this->getUomList($stitem_id));
		}

	}

	public function view ()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$mfshiftoutput = $this->_uses[$this->modeltype];
				
		$this->view->set('model', $mfshiftoutput);
		
		$mfshiftwaste = new MFShiftWasteCollection();
		
		$sh = $this->setSearchHandler($mfshiftwaste);
		
		$sh->setFields(array('id', 'waste_type', 'qty', 'uom_name'));
		
		$sh->addConstraint(new Constraint('mf_shift_outputs_id', '=', $mfshiftoutput->id));
		
		parent::index($mfshiftwaste, $sh);
		
		$this->view->set('clickaction','edit');
		$this->view->set('clickcontroller','mfshiftwastes');
		$this->view->set('page_title', $this->getPageName('', 'View'));

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();
		
		$sidebarlist['viewallshifts']= array('tag' => 'View All Shifts'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>'mfshifts'
													  ,'action'=>'index'
													  )
									  );
		
		$sidebarlist['newshift']= array('tag' => 'Add New Shift'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>'mfshifts'
													  ,'action'=>'new'
													  )
									  );
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$sidebarlist = array();
		
		$sidebarlist['viewshift']= array('tag' => 'View Shift'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>'mfshifts'
													  ,'action'=>'view'
													  ,'id'=>$mfshiftoutput->mf_shift_id
													  )
									  );
		$sidebarlist['edit']= array('tag' => 'Edit Output'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>$this->name
													  ,'action'=>'edit'
													  ,'id'=>$mfshiftoutput->id
													  )
									  );
		$sidebarlist['delete']= array('tag' => 'Delete Output'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>$this->name
													  ,'action'=>'delete'
													  ,'id'=>$mfshiftoutput->id
													  )
									  );
		$sidebarlist['addwastet']= array('tag' => 'Add Waste'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>'mfshiftwastes'
													  ,'action'=>'new'
													  ,'mf_shift_outputs_id'=>$mfshiftoutput->id
													  )
									  );
									  
		$sidebar->addList('Current Shift Output',$sidebarlist);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	
	}
	
	public function view_mfshift ()
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
		
		$mfshiftoutputs = new MFShiftOutputCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($mfshiftoutputs);
		
		$sh->addConstraint(new Constraint('mf_shift_id', '=', $this->_data['mf_shift_id']));
		
		parent::index($mfshiftoutputs, $sh);
		
		$this->view->set('clickaction','view');
		$this->view->set('page_title', $this->getPageName('', 'View'));
		
	}
	
/*
 * Ajax/Output Functions
 */	
	public function getItemData($_stitem_id = '', $_mf_centre_id = '') {
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);
		
		if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
		if(!empty($this->_data['mfcentre_id'])) { $_mf_centre_id=$this->_data['mfcentre_id']; }
		
		$output['run_time_speed']=array('data'=>$this->getRunTimeSpeed($_stitem_id, $_mf_centre_id),'is_array'=>FALSE);
	
		$output['uom_list']=array('data'=>$this->getUomList($_stitem_id),'is_array'=>TRUE);
			
		$output['works_orders']=array('data'=>$this->getWorkordersList($_stitem_id),'is_array'=>TRUE);
				
		if ($ajax)
		{
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $output;
		}
		
	}

/*
 * Private Functions
 */
	private function getUomList($_stitem_id = '')
	{

		$list = $this->_templateobject->getUomList($_stitem_id);
			
		return $list;
		
	}
	
	private function getRunTimeSpeed($_stitem_id = '', $_mf_centre_id = '')
	{
		
		$run_time_speed = 0;
		
		if(!empty($_stitem_id) && !empty($_mf_centre_id))
		{
			
			$mf_operation = DataObjectFactory::Factory('MFOperation');
			
			$mf_operation->loadBy(array('stitem_id', 'mfcentre_id'), array($_stitem_id, $_mf_centre_id));
			
			$run_time_speed = $mf_operation->volume_target;
		}
		
		return $run_time_speed;
	
	}
	
	private function getWorkordersList($_stitem_id = '')
	{
		
		$list = $this->_templateobject->getWorkOrders($_stitem_id);
		
		$list = array(''=>'None')+$list;

		return $list;
		
	}

/*
 * Protected Functions
 */
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Shift Outputs':$base), $action);
	}

}

// End of MfshiftoutputsController
