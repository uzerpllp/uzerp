<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhbinsController extends printController
{

	protected $version = '$Revision: 1.17 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('WHBin');
		
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors = array();

		$s_data = array();

		$whlocation = DataObjectFactory::Factory('WHLocation');

// Set context from calling module
		if (isset($this->_data['whlocation_id']))
		{
			$s_data['whlocation_id'] = $this->_data['whlocation_id'];
		}

		$this->setSearch('whbinsSearch', 'useDefault', $s_data);

		$whlocation_id = $this->search->getValue('whlocation_id');

		if (isset($whlocation_id))
		{
			$whlocation->load($whlocation_id);
		}

		$this->view->set('whlocation', $whlocation);
		$this->view->set('clickaction', 'view');

		parent::index(new WHBinCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['all_stores'] = array('tag' => 'all stores'
										  ,'link'=>array('modules'=>$this->_modules
														,'controller'=>'WHLocations'
														,'action'=>'index'
														,'whstore_id'=>$whlocation->whstore_id
														)
									);
		$sidebarlist['all_locations'] = array('tag' => 'locations for store '.$whlocation->whstore
											 ,'link'=>array('modules'=>$this->_modules
														   ,'controller'=>'WHLocations'
														   ,'action'=>'index'
														   ,'whstore_id'=>$whlocation->whstore_id
														   )
									);
		$sidebarlist['balances'] = array('tag' => 'Show Stock Balances'
										,'link' => array('modules'=>$this->_modules
														,'controller'=>'WHLocations'
														,'action'=>'viewBalances'
														,'id'=>$whlocation_id
														)
									);
		$sidebar->addList('Show',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('Location','View Bins for'));

	}

	public function view()
	{
		if (!$this->loadData())
		{
			$this->dataError('Error loading bin details');
			sendBack();
		}
		
		$id=$this->_data['id'];
		
		$transaction = $this->_uses[$this->modeltype];
		
		$this->view->set('transaction',$transaction);

		$whlocation = $this->getLocation($transaction->whlocation_id);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = $this->setGeneralSidebar($whlocation, $transaction);

		$cc = new ConstraintChain();
		$cc->add(new Constraint('whbin_id', '=', $this->_data['id']));
		
		$balance = DataObjectFactory::Factory('STBalance');
		$balance_count = $balance->getCount($cc);
		
		if ($balance_count > 0) {
			$sidebarlist['balances'] = array(
						'tag'	=> 'Balances for Bin',
						'link'	=> array('modules'		=> $this->_modules
										,'controller'	=> $this->name
										,'action'		=> 'viewbalances'
										,'id'			=> $id
										)
								);
		}
		$sidebar->addList('Show',$sidebarlist);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function viewBalances()
	{
		if ($this->isPrintDialog())
		{

			// set options
			$options = $this->reportOptions();
			
			// we use status in other print functions, however here we base it on if ajax print is or isn't set
			if(!$this->isPrinting())
			{
				return $options;
			}
			
		}
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$id				= $this->_data['id'];
		$transaction	= $this->_uses[$this->modeltype];
		
		$s_data = array();

		if (isset($this->_data['id']))
		{
			$s_data['whbin_id'] = $this->_data['id'];
		}
		elseif (isset($this->_data['Search']['whbin_id']))
		{
			$s_data['whbin_id'] = $id = $this->_data['Search']['whbin_id'];
		}

		if (isset($this->_data['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}
		elseif (isset($this->_data['Search']['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
		}

		$this->setSearch('whbinsSearch', 'withinBin', $s_data);

		$this->view->set('transaction',$transaction);

		$stbalances = new STBalanceCollection();

		$sh = $this->setSearchHandler($stbalances);

		$sh->setOrderby('stitem');
		
		parent::index($stbalances, $sh);
				
		$this->view->set('clickaction','viewTransactions');
		$this->view->set('clickcontroller','WHBins');
		$this->view->set('linkvaluefield','whbin_id');
		$this->view->set('no_ordering',true);

		$this->view->set('page_title',$this->getPageName('','View Balances for'));
		
		$sidebar = new SidebarController($this->view);

		$whlocation = $this->getLocation($transaction->whlocation_id);
		
		$sidebarlist = $this->setGeneralSidebar($whlocation, $transaction);
					                                     
		$sidebar->addList('Show', $sidebarlist);

		$sidebarlist = array();

		$sidebarlist['balances']= array(
					'tag' => 'Stock Balances',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>'WHLocations'
								   ,'action'=>'printAction'
								   ,'filename'=>'BalanceList_'.date('Y-m-d')
								   ,'printaction'=>'printBalanceList'
								   ,'id'=>$transaction->whlocation_id
								   ,'whbin_id'=>$id
								   )
							);

		$sidebar->addList('Reports', $sidebarlist);
		
        $this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function viewTransactions()
	{
		$s_data = array();

		if (isset($this->_data['id']))
		{
			$s_data['whbin_id'] = $id = $this->_data['id'];
		}
		elseif (isset($this->_data['Search']['whbin_id']))
		{
			$s_data['whbin_id'] = $id = $this->_data['Search']['whbin_id'];
		}

		if (isset($this->_data['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}
		elseif (isset($this->_data['Search']['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
		}

		if (!isset($this->_data['stitem_id']))
		{
			$s_data['created']['from']	= date(DATE_FORMAT,strtotime('-7 days'));
			$s_data['created']['to']	= date(DATE_FORMAT);
		}

		$this->setSearch('whbinsSearch', 'transactions', $s_data);
		
		$id				= $this->search->getValue('whbin_id');
		$item			= $this->search->getValue('stitem_id');
		$showbalances	= $this->search->getValue('balance');

		$bin = $this->_templateobject;
		$bin->load($id);
		$this->view->set('bin',$bin);

		$sttransactions = new STTransactionCollection();
		
		if (!isset($this->_data['orderby'])
			&& !isset($this->_data['page']))
		{
			$sh = new SearchHandler($sttransactions, false);
			$cc = $this->search->toConstraintChain();
			$sh->addConstraintChain($cc);
		}
		else
		{
			$sh = new SearchHandler($sttransactions);
		}

		$sh->extract();

		if (isset($this->search) && ($this->isPrintDialog() || $this->isPrinting()) )
		{
			if(!$this->isPrinting())
			{
				return $this->printCollection();
			}
			else
			{
				$sh->setLimit(0);
				$sttransactions->load($sh);
				return $this->printCollection($sttransactions);
			}
		}
		else
		{
			$sttransactions->load($sh);
		}

		$this->view->set('sttransactions',$sttransactions);

		$this->view->set('clickaction','view');
		$this->view->set('clickcontroller','STItems');
		$this->view->set('linkfield','id');
		$this->view->set('linkvaluefield','stitem_id');
		$this->view->set('num_pages',$sttransactions->num_pages);
		$this->view->set('cur_page',$sttransactions->cur_page);
		$this->view->set('no_ordering',true);

		$this->view->set('page_title',$this->getPageName('','View Transactions for'));
		
		$sidebar = new SidebarController($this->view);

		$whlocation = $this->getLocation($bin->whlocation_id);
		
		$sidebarlist = $this->setGeneralSidebar($whlocation, $bin);
				                                     
		$sidebar->addList('Show', $sidebarlist);
							
        $this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	protected function getLocation($whlocation_id)
	{
		$whlocation = DataObjectFactory::Factory('WHLocation');

		$whlocation->load($whlocation_id);

		$this->view->set('whstore', $whlocation->whstore);

		return $whlocation;
	}

	protected function setGeneralSidebar ($whlocation, $bin)
	{
		$sidebarlist = array();

		$sidebarlist['allStores'] = array(
					'tag'	=> 'All Stores',
					'link'	=> array('modules'		=> $this->_modules
								 	,'controller'	=> 'WHStores'
								 	,'action'		=> 'index'
								 	)
							);
		$sidebarlist['allLocations'] = array(
					'tag'	=> 'Locations for '.$whlocation->whstore,
					'link'	=> array('modules'		=> $this->_modules
								 	,'controller'	=> 'WHLocations'
								 	,'action'		=> 'index'
								 	,'whstore_id'	=> $whlocation->whstore_id
								 	)
							);
		$sidebarlist['location'] = array(
					'tag'	=> 'Location '.$bin->whlocation,
					'link'	=> array('modules'		=> $this->_modules
								 	,'controller'	=> 'WHLocations'
								 	,'action'		=> 'view'
								 	,'id'			=> $bin->whlocation_id
								 	)
							);

		return $sidebarlist;
	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'bins':$base), $action);
	}

}

// End of WhbinsController
