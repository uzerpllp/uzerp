<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MfshiftsController extends ProductionRecordingController
{

	protected $version = '$Revision: 1.5 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('MFShift');
		
		$this->uses($this->_templateobject);
		
	}

	public function index()
	{

		$s_data = array();

		$this->setSearch('mfshiftsSearch', 'useDefault', $s_data);
		
		$this->view->set('clickaction', 'view');
		
		parent::index(new MFShiftCollection($this->_templateobject));
				
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'Add New Shift'
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
		
		$mfshift = $this->_uses[$this->modeltype];
		
		$mfdepts = $mfshift->getAllDept();
		$this->view->set('mfdepts', $mfdepts);
		
		$mfcentres = array();
		
		if (count($mfdepts)>0)
		{
			if (isset($_POST[$this->modeltype]['mf_dept_id']))
			{
				$mfdept_id = $_POST[$this->modeltype]['mf_dept_id'];
			}
			elseif ($mfshift->isLoaded())
			{
				$mfdept_id = $mfshift->mf_dept_id;
			}
			elseif (isset($this->_data['mf_dept_id']))
			{
				$mfdept_id = $this->_data['mf_dept_id'];
			} else {
				$mfdept_id = key($mfdepts);
			}
			$mfcentres = $mfshift->getAllCentres($mfdept_id);
		}
		
		$this->view->set('mfcentres', $mfcentres);
		
	}
	
	public function save()
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}

		$flash = Flash::Instance();
		
		$errors = array();
		
		if(parent::save_model($this->modeltype, '', $errors))
		{
			sendTo($this->name
				  ,'view'
				  ,$this->_modules
				  ,array($this->saved_model->idField=>$this->saved_model->{$this->saved_model->idField}));
		}
		
		$flash->addErrors($errors);
		
		$flash->addError('Failed to save '.$this->modeltype);
		
		$this->refresh();
		
	}

	public function getCentres($_mf_dept_id = '')
	{
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['mf_dept_id'])) { $_mf_dept_id=$this->_data['mf_dept_id']; }
		}
		
// Used by Ajax to return UoM list after selecting the Product Line item
		$mfshift = $this->_uses[$this->modeltype];
		$mfcentres = $mfshift->getAllCentres($_mf_dept_id);
				
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$mfcentres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $mfcentres;
		}
	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'MF Shifts':$base), $action);
	}

}

// End of MfshiftsController
