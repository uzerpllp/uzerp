<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SdcomplaintsController extends ComplaintsController
{
	
	protected $version = '$Revision: 1.33 $';
	
	protected $_templateobject;
	
	public function __construct($module = NULL, $action = NULL)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('SDComplaint');
		
		$this->uses($this->_templateobject);
	
	}
	
	public function index()
	{
		
		$s_data = array('type' => 'SD');
		
		$this->setSearch('ComplaintSearch', 'sdsearch', $s_data);
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new SDComplaintCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'new'
					),
					'tag' => 'New SD Complaint'
				),
				'view_rr' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'RRComplaints',
						'action'		=> 'index'
					),
					'tag' => 'View RR Complaints'
				)
			)
		);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function _new()
	{
		
		parent::_new();
		
		$complaint = $this->_uses[$this->modeltype];
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array(
			'return' => array(
				'tag'	=> 'Return to SD Complaints',
				'link'	=> array(
					'modules'		=> $this->_modules,
					'controller'	=> $this->name,
					'action'		=> 'index'
				)
			)
		);
        
		$sidebar->addList('Action', $sidebarlist);
		
		if (!$complaint->isLoaded())
		{
			
			$glparams			= DataObjectFactory::Factory('GLParams');
			$default_currency	= utf8_decode($glparams->base_currency());
			
			$this->view->set('default_currency', $default_currency);
			
		}
		else
		{
			
			$sidebarlist = array(
				'report' => array(
					'tag'	=> 'Print Current Complaint',
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'printDialog',
						'printaction'	=> 'printComplaint',
						'id'			=> $this->_data['id'],
						'filename'		=> 'Complaint-SD' . $complaint->complaint_number . '_' . date('d-m-Y'),
						'type'			=> 'New'
					)
				)
			);
			
			$sidebar->addList('Reports', $sidebarlist);
			
		}
		
		$retailer	= DataObjectFactory::Factory('SLCustomer');
		$retailers	= $retailer->getAll(NULL, FALSE, TRUE);
		$this->view->set('retailers', $retailers);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	protected function getPageName($base = NULL, $action = NULL)
	{
		return parent::getPageName((empty($base) ? 'SD Complaints' : $base), $action);
	}
	
	public function printComplaint($_status = 'generate')
	{
		
		$filename = (!empty($this->_data['filename']))?$this->_data['filename']:'SD_complaint_' . date('d-m-Y');
		
		return parent::printComplaint($_status, $filename);
		
	}
	
}

// end of SdcomplaintsController.php