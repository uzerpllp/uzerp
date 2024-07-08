<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class RrcomplaintsController extends ComplaintsController
{
	
	protected $version = '$Revision: 1.27 $';
	
	protected $_templateobject;
	
	public function __construct($module = NULL, $action = NULL)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('RRComplaint');
		
		$this->uses($this->_templateobject);
	
	}
	
	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$s_data = array('type' => 'RR');
		
		$this->setSearch('ComplaintSearch', 'rrsearch', $s_data);
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new RRComplaintCollection($this->_templateobject));
		
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
					'tag' => 'New RR Complaint'
				),
				'view_sd' => array(
					'link' => array(
						'modules'		=> $this->_modules,
						'controller'	=> 'SDComplaints',
						'action'		=> 'index'
					),
					'tag' => 'View SD Complaints'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function _new()
	{
		
		parent::_new();
		
		$rrcomplaint = $this->_uses[$this->modeltype];
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array(
			'return' => array(
				'tag'	=> 'Return to RR Complaints',
				'link'	=> array(
					'modules'		=> $this->_modules,
					'controller'	=> $this->name,
					'action'		=> 'index'
				)
			)
		);
		
		$sidebar->addList('Action', $sidebarlist);
		
		if (!$rrcomplaint->isLoaded())
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
						'filename'		=> 'Complaint-RR' . $complaint->complaint_number . '_' . date('d-m-Y'),
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
		return parent::getPageName((empty($base) ? 'RR Complaints' : $base), $action);
	}

	/* output functions */
	
	public function printComplaint($_status = 'generate')
	{
		
		$filename = (!empty($this->_data['filename']))?$this->_data['filename']:'RR_complaint_' . date('d-m-Y');
		
		return parent::printComplaint($_status, $filename);
		
	}
	
}

// end of RrcompaintController.php