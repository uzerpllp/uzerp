<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhlocationsController extends printController
{

	protected $version = '$Revision: 1.33 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('WHLocation');

		$this->uses($this->_templateobject);

	}

	public function index()
	{
		
		$errors = array();
		$s_data = array();
		
		// Set context from calling module
		if (isset($this->_data['whstore_id']))
		{
			$s_data['whstore_id'] = $this->_data['whstore_id'];
		}
		
		$this->setSearch('whlocationsSearch', 'useDefault', $s_data);

		$id = $this->search->getValue('whstore_id');
		$this->view->set('clickaction', 'view');
		
		parent::index(new WHLocationCollection($this->_templateobject));

		$store = DataObjectFactory::Factory('WHStore');
		$store->load($id);
		
		$this->view->set('whstore', $store);
		
		$sidebar		= new SidebarController($this->view);
		$sidebarlist	= array();
		
		$sidebarlist['stores'] = array(
			'tag'	=> 'View All Stores',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'index'
			)
		);
		
		$sidebar->addList('Actions', $sidebarlist);
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$id				= $this->_data['id'];
		$transaction	= $this->_uses[$this->modeltype];
		
		if ($transaction->bins->count() > 0)
		{
			sendTo('WHBins', 'index', $this->_modules, array('whlocation_id' => $this->_data['id']));
		}
				
		$this->view->set('transaction', $transaction);

		$sidebar		= new SidebarController($this->view);
		$sidebarlist	= array();
		
		$sidebarlist['stores'] = array(
			'tag'	=> 'All Stores',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'index'
			)
		);
		
		$sidebarlist['locations'] = array(
			'tag'	=> 'Locations for Store ' . $transaction->whstore,
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'view',
				'id'			=> $transaction->whstore_id
			)
		);
		
		$sidebar->addList('Show', $sidebarlist);

		if ($transaction->isBalanceEnabled())
		{
			
			$sidebarlist = array();
			
			$sidebarlist['balances'] = array(
				'tag'	=> 'Show Stock Balances',
				'link'	=> array(
					'modules'		=> $this->_modules,
					'controller'	=> $this->name,
					'action'		=> 'viewBalances',
					'id'			=> $id
				)
			);
			
			$sidebar->addList('This Location', $sidebarlist);
			
		}
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		$this->view->set('page_title', $this->getPageName('Location'));

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
			$id = $this->_data['id'];
			$s_data['whlocation_id'] = $id;
		} 
		elseif (isset($this->_data['Search']['whlocation_id']))
		{
			$id = $this->_data['Search']['whlocation_id'];
			$s_data['whlocation_id'] = $id;
		}
		
		if (isset($this->_data['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}
		elseif (isset($this->_data['Search']['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['Search']['stitem_id'];
		}
		
		$this->setSearch('whlocationsSearch', 'withinLocation', $s_data);
		
		$this->view->set('transaction', $transaction);
		$this->view->set('page_title', $this->getPageName('Location','View Balances for'));
		
		$stbalances	= new STBalanceCollection();
		$sh = $this->setSearchHandler($stbalances);

		$sh->setOrderby('stitem');
		
		parent::index($stbalances, $sh);
		
		$this->view->set('clickaction', 'viewTransactions');
		$this->view->set('clickcontroller', 'WHLocations');
		$this->view->set('linkvaluefield', 'whlocation_id');
		$this->view->set('no_ordering', TRUE);

		$sidebar		= new SidebarController($this->view);
		$sidebarlist	= array();
		
		$sidebarlist['stores'] = array(
			'tag'	=> 'All Stores',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'index'
			)
		);
		
		$sidebarlist['locations'] = array(
			'tag'	=> 'Locations for Store ' . $transaction->whstore,
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'index',
				'id'			=> $transaction->whstore_id
			)
		);
		
		$sidebarlist['thisLocation'] = array(
			'tag'	=> 'Location Detail',
            'link'	=> array(
            	'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'view',
				'id'			=> $id
			)
		);
                                     
		$sidebar->addList('Show', $sidebarlist);
        $this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function viewTransactions()
	{
		
		$s_data = array();
		
		if (isset($this->_data['id']))
		{
			$id = $this->_data['id'];
			$s_data['whlocation_id'] = $id;
		}
		elseif (isset($this->_data['Search']['whlocation_id']))
		{
			$id = $this->_data['Search']['whlocation_id'];
			$s_data['whlocation_id'] = $id;
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
			$s_data['created']['from']	= date(DATE_FORMAT, strtotime('-7 days'));
			$s_data['created']['to']	= date(DATE_FORMAT);
		}
		
		$this->setSearch('whlocationsSearch', 'transactions', $s_data);
		
		$id				= $this->search->getValue('whlocation_id');
		$item			= $this->search->getValue('stitem_id');
		$showbalances	= $this->search->getValue('balance');
		$location		= $this->_templateobject;
		
		$location->load($id);
		$this->view->set('location', $location);
		
		$sttransactions = new STTransactionCollection();
		
		if (!isset($this->_data['orderby']) && !isset($this->_data['page']))
		{
			$sh = new SearchHandler($sttransactions, FALSE);
			$cc = $this->search->toConstraintChain();
			$sh->addConstraintChain($cc);
		}
		else
		{
			$sh = new SearchHandler($sttransactions);
		}	
		
		$sh->extract();
		
		if (isset($this->search) && ($this->isPrintDialog() || $this->isPrinting()))
		{
			
			if (!$this->isPrinting())
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
		
		$this->view->set('sttransactions', $sttransactions);
		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'STItems');
		$this->view->set('linkfield', 'id');
		$this->view->set('linkvaluefield', 'stitem_id');
		$this->view->set('num_records', $sttransactions->num_records);
		$this->view->set('num_pages', $sttransactions->num_pages);
		$this->view->set('cur_page', $sttransactions->cur_page);
		$this->view->set('no_ordering', TRUE);

		$sidebar		= new SidebarController($this->view);
		$sidebarlist	= array();
		
		$sidebarlist['stores'] = array(
			'tag'	=> 'All Stores',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'index',
			)
		);
		
		$sidebarlist['locations'] = array(
			'tag'	=> 'Locations for Store ' . $location->whstore,
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'WHStores',
				'action'		=> 'view',
				'id'			=> $location->whstore_id
			)
		);
		
		$sidebarlist['thisLocation'] = array(
        	'tag'	=> 'Location Detail',
            'link'	=> array(
            	'modules'		=> $this->_modules,
				'controller'	=> $this->name,
				'action'		=> 'view',
				'id'			=> $location->id
			)
		);
        
		$sidebar->addList('Show', $sidebarlist);
        $this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
		
	}

	public function printMultipleBalance()
	{
		
		$flash	= Flash::Instance();	
		$errors	= array();
		$extra	= array();
		
		if (!isset($this->_data['WHLocation_location']) || empty($this->_data['WHLocation_location']))
		{
			$errors[] = 'Location is a required field';
		}		
		if (!isset($this->_data['WHBin_bins']) || empty($this->_data['WHBin_bins']))
		{
			$errors[] = 'Bin is a required field';
		}

		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
			sendBack();
		}
		
		// Check and set default print set
		$userPreferences	= UserPreferences::instance(EGS_USERNAME);
        $defaultPrinter		= $userPreferences->getPreferenceValue('default_printer','shared');
         
        if (empty($defaultPrinter))
        {
        	$flash->addError('Cannot print without a default printer set');
        	sendBack();
        }
        else
        {
        	$this->_data['printtype']	= 'pdf';
        	$this->_data['printaction']	= 'Print';
        	$this->_data['printer']		= $defaultPrinter;
        }

        // construct constraint against bins
		$stbalances = new STBalanceCollection();
		$sh = new SearchHandler($stbalances, FALSE);
		$sh->addConstraint(new Constraint('balance', '<>', 0));
		$sh->addConstraint(new Constraint('whstore_id', '=', $this->_data['WHStore_store']));
		$sh->addConstraint(new Constraint('whlocation_id', '=', $this->_data['WHLocation_location']));
		$binlist = implode(',', $this->_data['WHBin_bins']);
		$sh->addConstraint(new Constraint('whbin_id', 'in', '('.$binlist.')'));
		$sh->setOrderby(array('whbin', 'stitem'), array('ASC', 'ASC'));
		$stbalances->load($sh);
		
		// set title
		$WHLocation = $this->_templateobject;
		$WHLocation->load($this->_data['WHLocation_location']);
		
		$WHStore = DataObjectFactory::Factory('WHStore');
		$WHStore->load($this->_data['WHStore_store']);
		
		$extra['title'] = 'Stock Balance for ' . $WHStore->description . ' / ' . $WHLocation->description . ' as at ' . un_fix_date(fix_date(date(DATE_FORMAT)));
		
		// construct xml
		$xml = $this->generateXML(
			array(
				'model'					=> $stbalances,
				'load_relationships'	=> FALSE,
				'extra'					=> $extra
			)
		);

		// build a basic list of options
		$options = array(
			'report'	=> 'MF_MultipleStockBalance',
			'xmlSource'	=> $xml
		);
		
		// construct the document, caputre the response
		$response = json_decode($this->constructOutput($this->_data, $options));
	
		// output success / failure message
		if ($response->status !== TRUE)
		{
			$flash->addError("Error printing document » " . $response->message);
		}
		else
		{
			$flash->addMessage("Document printed successfully");
		}
		
		// return back to eglet
		sendBack();
		
	}
	
	public function getBinList($_id = '')
	{

		// Function called by Ajax Request to return list of Bins
		// depending on selected Location 

		if (isset($this->_data['ajax']))
		{
			
			if (!empty($this->_data['id']))
			{
				$_id = $this->_data['id'];
			}
			
		}
		
		$to_bins = array();
		
		if (is_numeric($_id))
		{
			
			$location = $this->_templateobject;
			$location->load($_id);
			
			if ($location->isLoaded() && $location->isBinControlled())
			{
				$to_bins = $location->getBinList();
			}
			
		}
		
		if (isset($this->_data['id']))
		{
			$this->view->set('options', $to_bins);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $to_bins;
		}
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'locations':$base), $action);
	}
	
}

// end of WhlocationsController
