<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlbalancesController extends printController
{

	protected $version = '$Revision: 1.22 $';
	
	protected $_templateobject;
	
	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('GLBalance');
		
		$this->uses($this->_templateobject);

	}

	public function index()
	{

		$errors = array();
	
		$this->setSearch('glbalancesSearch', 'useDefault');
		$this->view->set('clickaction', '#');
		
		$glbalances = new GLBalanceCollection($this->_templateobject);
		
		parent::index($glbalances);		
		
		$page_credit_total = 0;
		$page_debit_total = 0;
		
		foreach ($glbalances->getArray() as $row)
		{
			$page_credit_total = bcadd($page_credit_total, $row['credit']);
			$page_debit_total = bcadd($page_debit_total, $row['debit']);
		}
		
		$this->view->set('page_total', number_format(bcsub($page_debit_total, $page_credit_total), 2));
		$this->view->set('page_credit_total', number_format($page_credit_total, 2));
		$this->view->set('page_debit_total', number_format($page_debit_total, 2));
		
		$sidebar		= new SidebarController($this->view);
		$sidebarlist	= array();
		
		$sidebarlist['viewaccounts'] = array(
			'tag'	=> 'View All Accounts',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'glaccounts',
				'action'		=> 'index'
			)
		);

		$sidebarlist['viewcentres'] = array(
			'tag'	=> 'View All Centres',
			'link'	=> array(
				'modules'		=> $this->_modules,
				'controller'	=> 'glcentres',
				'action'		=> 'index'
			 )
		);

		$sidebar->addList('Actions', $sidebarlist);
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}
	
	public function trialBalance()
	{

		$errors = array();
		
		$this->setSearch('glbalancesSearch', 'trialBalance');
		
		$glperiods_id = $this->search->getValue('glperiods_id');
		
		$balance	= $this->_uses[$this->modeltype];
		$current	= $balance->getSum($glperiods_id);
		$period		= DataObjectFactory::Factory('GLPeriod');
		
		if ($period->load($glperiods_id))
		{
			$ytd_periods = $period->getIdsYTD($period->period, $period->year);
			$ytd = $balance->getSum($ytd_periods);
		}
		else
		{
			$ytd_periods = array();
			$ytd = 0;
		}
		
		$this->view->set('current', $current);
		$this->view->set('ytd', $ytd);

		if (isset($this->_data['orderby']) || isset($this->_data['page']))
		{
			$use_saved_search = TRUE;
		}
		else
		{
			$use_saved_search = FALSE;
		}
		
		$collection = new GLBalanceCollection($this->_templateobject);
		$collection->getYTD($ytd_periods, $this->isPrinting(), $use_saved_search);
		
		$this->view->set('collection', $collection);
		$this->view->set(strtolower($collection->getModelName()) . 's', $collection);
		
		if (!$this->isPrinting() && !$collection->isEmpty())
		{
			$this->view->set('num_pages', $collection->num_pages);
			$this->view->set('cur_page', $collection->cur_page);
		}
		
		$this->view->set('row_count', $collection->num_records);
		$this->view->set('glperiods_id', $glperiods_id);
		
		$period = DataObjectFactory::Factory('GLPeriod');
		$period->load($glperiods_id);
		
		$this->view->set('period', $period->getIdentifierValue());
		$this->view->set('page_title', $this->getPageName('Trial Balance', 'View'));
		
	}
		
	public function printtrialbalance() {
			
		$options = $this->reportOptions();

		// build options array
		$options['filename'] = 'TrialBalance_' . date('d-m-Y');
		
		// we use status in other print functions, however here we base it on if ajax print is or isn't set
		if (!$this->isPrinting())
		{
			return $options;
		}
		
		if (isset($this->_data['encoded_query_data']))
		{
			$original_data = $this->decode_original_form_data($this->_data['encoded_query_data']);
			$this->_data['Search'] = $original_data['Search'];
		}
		
		// get the saved search
		$this->setSearch('glbalancesSearch', 'trialBalance');
		
		// get the gl periods value from the saved search
		$glperiods_id = $this->search->getValue('glperiods_id');
		
		// get current total
		$balance = $this->_uses[$this->modeltype];
		$current = $balance->getSum($glperiods_id);
		
		// get ytd total
		$period = DataObjectFactory::Factory('GLPeriod');
		
		if ($period->load($glperiods_id))
		{
			$ytd_periods = $period->getIdsYTD($period->period, $period->year);
			$ytd = $balance->getSum($ytd_periods);
		}
		else
		{
			$ytd_periods = array();
			$ytd = 0;
		}
		
		// load collection
		$collection = new GLBalanceCollection($balance);
		$collection->getYTD($ytd_periods, TRUE, TRUE);
				
		$col_arr = array();
		
		// construct data array
		foreach ($collection as $key => $value)
		{
			
			$ytd_actual		= $value->value;
			$ytd_budget		= $value->getYTDBudget($glperiods_id);
			$month_actual	= $value->getCurrent($glperiods_id);
			$month_budget	= $value->getCurrentBudget($glperiods_id);
			
			$col_arr[] = array(
				'id'				=> $value->id,
				'centre'			=> $value->centre,
				'glcentre_id'		=> $value->glcentre_id,
				'account'			=> $value->account,
				'glaccount_id'		=> $value->glaccount_id,
				'period'			=> $period->getIdentifierValue(),
				'ytd_actual'		=> sprintf("%01.2f", $ytd_actual),
				'ytd_budget'		=> sprintf("%01.2f", $ytd_budget),
				'ytd_variance'		=> sprintf("%01.2f", $ytd_budget-$ytd_actual),
				'month_actual'		=> sprintf("%01.2f", $month_actual),
				'month_budget'		=> sprintf("%01.2f", $month_budget),
				'month_variance'	=> sprintf("%01.2f", $month_budget-$month_actual)
			);
		}
		
		if ($this->_data['print']['printtype'] !== 'csv')
		{
			
			// add totals to array
			$col_arr[] = array(
				'period'		=> 'Totals',
				'ytd_actual'	=> $ytd,
				'month_actual'	=> $current
			);
			
		}
		
		// construct headings array
		$cols = array(
			'account'			=> 'Account',
			'centre'			=> 'Centre',
			'period'			=> 'Period',
			'ytd_actual'		=> 'YTD Actual',
			'ytd_budget'		=> 'YTD Budget',
			'ytd_variance'		=> 'YTD Variance',
			'month_actual'		=> 'Month Actual',
			'month_budget'		=> 'Month Budget',
			'month_variance'	=> 'Month Variance'
		);
		
		if ($this->_data['print']['printtype'] === 'csv')
		{
			
			$csv = $this->generate_csv(
				$this->_data['print'],
				$col_arr,
				array_keys(current($col_arr))
			);
			
			$options['csv_source'] = $csv;
			
		}
		else
		{
			
			// build XSL
			$xsl = $this->build_custom_xsl(
				$collection,
				'PrintCollection',
				'Trial Balance - ' . date('d/m/Y'),
				$cols,
				$this->_data['col_widths']
			);
			
			// build XML
			$xml = $this->build_custom_xml($col_arr, $cols);
			
			// set XSL / XML to options
			$options['xslSource'] = $xsl;
			$options['xmlSource'] = $xml;
		}
		
		// execute the print output function, echo the returned json for jquery
		echo $this->constructOutput($this->_data['print'], $options);
		exit;
	
	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'general_ledger_balances':$base), $action);
	}
}

// end of GlbalancesController
