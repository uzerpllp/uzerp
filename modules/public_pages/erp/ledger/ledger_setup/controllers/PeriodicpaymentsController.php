<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PeriodicpaymentsController extends LedgerController
{

	protected $version = '$Revision: 1.16 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('PeriodicPayment');
		
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$errors = array();
		
		$s_data = array();
		
// Set context from calling module
		if (isset($this->_data['cbaccount_id'])) {
			$s_data['cbaccount_id'] = $this->_data['cbaccount_id'];
		}
		if (isset($this->_data['company_id'])) {
			$s_data['company_id'] = $this->_data['company_id'];
		}
		if (isset($this->_data['source'])) {
			$s_data['source'] = $this->_data['source'];
		}
		if (isset($this->_data['status'])) {
			$s_data['status'] = $this->_data['status'];
		}
		if (isset($this->_data['frequency'])) {
			$s_data['frequency'] = $this->_data['frequency'];
		}

		$this->setSearch('PeriodicPaymentsSearch', 'useDefault', $s_data);
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new PeriodicPaymentCollection($this->_templateobject));		
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['new']= array(
							'tag'	=> 'New Payment',
							'link'	=> array('modules'		=> $this->_modules
											,'controller'	=> $this->name
											,'action'		=> 'new'
											)
				);
		
		$sidebar->addList('Actions',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();

		$gl_account = DataObjectFactory::Factory('GLAccount');
		
		$gl_accounts = $gl_account->getAll();
		
		$this->view->set('gl_accounts',$gl_accounts);
		
		$pp = $this->_uses[$this->modeltype];
		
		if (isset($_POST[$this->modeltype]['source']))
		{
			$default_source = $_POST[$this->modeltype]['source'];
		}
		elseif ($pp->isLoaded())
		{
			$default_source = $pp->source;
		}
		else
		{
// following line commented out and replacing with next two lines
// due to possible php bug that causes problems on template
//  		$default_source=key($pp->getEnumOptions('source'));
			$sources		= $pp->getEnumOptions('source');
			$default_source	= key($sources);
		}
		
		$companies = $this->getCompanies($default_source);
		
		$this->view->set('companies',$companies);
		
		if (isset($_POST[$this->modeltype]['company_id']))
		{
			$default_company_id = $_POST[$this->modeltype]['company_id'];
		}
		elseif ($pp->isLoaded())
		{
			$default_company_id = $pp->company_id;
		}
		else
		{
			$default_company_id = key($companies);
		}
		
		$this->view->set('people', $this->getPeople($default_company_id));
		
		$cb_account = DataObjectFactory::Factory('CBAccount');
		
		$cb_accounts = $cb_account->getAll();
		
		if (isset($_POST[$this->modeltype]['cb_account_id']))
		{
			$default_cb_account_id = $_POST[$this->modeltype]['cb_account_id'];
		}
		elseif ($pp->isLoaded())
		{
			$default_cb_account_id = $pp->cb_account_id;
		}
		else
		{
			$cb_account->getDefaultAccount(key($cb_accounts));
			
			$default_cb_account_id = $cb_account->{$cb_account->idField};
		}
		
		$this->view->set('cb_account_id', $default_cb_account_id);
		
		if ($pp->isLoaded())
		{
			$this->view->set('currency', $pp->currency_id);
		}
		else
		{
			$this->view->set('currency', $this->getCurrencyId($default_company_id, $default_cb_account_id, $default_source));
		}	
		
		if (isset($_POST[$this->modeltype]['glaccount_id']))
		{
			$default_glaccount_id = $_POST[$this->modeltype]['glaccount_id'];
		}
		elseif ($pp->isLoaded())
		{
			$default_glaccount_id = $pp->glaccount_id;
		}
		else
		{
			$default_glaccount_id = key($gl_accounts);
		}
		
		$this->view->set('gl_centres', $this->getCentres($default_glaccount_id));

	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash=Flash::Instance();
		$errors = array();
		
		$data = $this->_data[$this->modeltype];
		
		if (empty($data['id']))
		{
			$data['next_due_date']	= $data['start_date'];
			$data['status']			= 'A';
		}
		
		if ($data['status']=='A'
			&& !empty($data['glaccount_id'])
			&& !empty($data['glcentre_id'])
			&& $data['source'] != 'SR'
			&& $data['source'] != 'PP')
		{
			$data['glaccount_centre_id'] = GLAccountCentre::getAccountCentreId($data['glaccount_id'], $data['glcentre_id'], $errors);
		}
		else
		{
			unset($data['glaccount_id']);
			unset($data['glcentre_id']);
			unset($data['glaccount_centre_id']);
		}
		
		if (empty($data['net_value'])
		&& empty($data['tax_value'])
		&& empty($data['gross_value']))
		{
			$flash->addError('No value entered');
		}
		else
		{
			if (empty($data['net_value']) || $data['net_value']==0)
			{
				$data['net_value'] = $data['gross_value'];
			}
			if (empty($data['tax_value']))
			{
				$data['tax_value'] = 0;
			}
			if (empty($data['gross_value']) || $data['gross_value']==0)
			{
				$data['gross_value'] = bcadd($data['net_value'], $data['tax_value']);
			}
		}
		
		if(count($errors)===0 && parent::save_model($this->modeltype, $data))
		{
			sendTo($this->name, 'index', $this->_modules);
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	public function getCompanies ($source)
	{
// Used by Ajax to return list of Companies after selecting the periodic payment source

		switch ($source) {
			case 'CR':
			case 'CP':
				$company				= DataObjectFactory::Factory('Company');
				$companylist			= $company->getAll();
				break;
			case 'SR':
				$company				= DataObjectFactory::Factory('SLCustomer');
				$company->idField			= 'company_id';
				$company->identifierField	= 'name';
				$companylist			= $company->getAll();
				break;
			case 'PP':
				$company				= DataObjectFactory::Factory('PLSupplier');
				$company->idField			= 'company_id';
				$company->identifierField	= 'name';
				$companylist			= $company->getAll();
				break;
		}
		return $companylist;
				
	}
	
	public function getCompanyList ($_id = '')
	{
// Used by Ajax to return list of Companies after selecting the periodic payment source
		/*
		 * We only want to override the function parameters if the call has come from
		 * an ajax request, simply overwriting them as we were leads to a mix up in
		 * values
		 */
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		$companies = $this->getCompanies($_id);
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$companies);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $companies;
		}
		
	}

	public function getCurrencyId ($_company_id = '', $_cbaccount_id = '', $_source = '')
	{
// Used by Ajax to return the currency of the Customer/Supplier/Account after selecting the periodic payment source
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['company_id'])) { $_company_id=$this->_data['company_id']; }
			if(!empty($this->_data['cb_account_id'])) { $_cbaccount_id=$this->_data['cb_account_id']; }
			if(!empty($this->_data['source'])) { $_source=$this->_data['source']; }
		}
		
		switch ($_source)
		{
			case 'CR':
			case 'CP':
				$company = DataObjectFactory::Factory('CBAccount');
				
				$company->load($_cbaccount_id);
				
				break;
			case 'SR':
				$company = DataObjectFactory::Factory('SLCustomer');
				
				$cc	= new ConstraintChain();
				
				$cc->add(new Constraint('company_id', '=', $_company_id));
				
				$company->loadBy($cc);
				
				break;
			case 'PP':
				$company = DataObjectFactory::Factory('PLSupplier');
				
				$cc = new ConstraintChain();
				
				$cc->add(new Constraint('company_id', '=', $_company_id));
				
				$company->loadBy($cc);
				
				break;
		}

		if ($company)
		{
			$id = $company->currency_id;
		} else {
			$id = '';
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $id);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $id;
		}
	}

	public function getPeople($_company_id = '')
	{

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['company_id'])) { $_company_id=$this->_data['company_id']; }
		}
		
		$company = DataObjectFactory::Factory('Company');
		
		$company->load($_company_id);
		
		$people = $company->getPeople();
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$people);
			$this->setTemplateName('select_options');
		}
		
		return $people;
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'periodic_payments':$base), $action);
	}

}

// End of PeriodicpaymentsController

