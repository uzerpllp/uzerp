<?php
/**
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class VatController extends printController
{

	protected $version = '$Revision: 1.30 $';
	protected $_templateobject;
	protected $titles;
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('VatReturn');
		
		$this->uses($this->_templateobject);
		
		$this->titles=array(4=>'Inputs', 6=>'Outputs', 8=>'EU Sales', 9=>'EU Purchases', 98=>'Postponed VAT Purchases',99=>'Reverse Charge Purchases');

	}
	
	public function index()
	{
		$errors = array();
		$s_data = array();
		$flash = Flash::Instance();

		$mtd_config = OauthStorage::getconfig('mtd-vat');
		if ($mtd_config === null) {
			$flash->addWarning('Making Tax Digital for VAT is not configured');
			$this->view->set('mtd_configured', false);
		} else {
			$this->view->set('mtd_configured', true);
			$mtd = new MTD();
			$result = $mtd->refreshToken();
			if ($result === true) {
				$this->view->set('mtd_authorised', true);
			}
		}

		$this->setSearch('VatSearch', 'useDefault');
		parent::index(new VatReturnCollection($this->_templateobject));
		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
			sendBack();
		}

		$sidebar = new SidebarController($this->view);

		$returns_sidebar = [];
		$returns_sidebar['inputjournal'] = array(
			'link'=>array('modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'enter_journal'
						 ,'vat_type'=>'input'
						 ),
			'tag'=>'VAT Input Journal'
		);

		$returns_sidebar['outputjournal'] = array(
			'link'=>array('modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'enter_journal'
						 ,'vat_type'=>'output'
						 ),
			'tag'=>'VAT Output Journal'
		);

		$returns_sidebar['pvaEntry'] = array(
			'link'=>array('modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'enterPVAEntry'
						 ),
			'tag'=>'import_pva'
		);

		$sidebar->addList('Actions', $returns_sidebar);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		$this->view->set('page_title','Vat Returns');
		$this->view->set('clickaction', 'view');
	}

	public function enter_journal()
	{
		//$flash=Flash::Instance();
		
		//$errors=array();
		
		if (!$this->checkParams('vat_type'))
		{
			sendBack();
		}
		
		$gl_account = DataObjectFactory::Factory('GLAccount');
		$gl_accounts = $gl_account->nonControlAccounts();
		
		$this->view->set('gl_accounts',$gl_accounts);
		
		if (isset($this->_data['glaccount_id']))
		{
			$account_id = $this->_data['glaccount_id'];
		}
		else
		{
			$account_id = key($gl_accounts);
		}
		
		$gl_account->load($account_id);
		$this->view->set('gl_centres',$gl_account->getCentres());

		$period = DataObjectFactory::Factory('GLPeriod');
		$current = $period->getPeriod(un_fix_date(fix_date(date(DATE_FORMAT))));
		$this->view->set('periods', $period->getOpenPeriods(false));
		$this->view->set('current_period', $current['id']);
		
		$this->view->set('vat_type', $this->_data['vat_type']);
		$this->view->set('vat', DataObjectFactory::Factory('Vat'));
		
		$this->view->set('page_title', $this->getPageName('', 'Enter '.$this->_data['vat_type'].' Journal'));
	}

	/**
	 * Display a form for a Postponed VAT Accounting Entry
	 *
	 * @return void
	 */
	public function enterPVAEntry() {
		$period = DataObjectFactory::Factory('GLPeriod');

		if(isset($this->_data['Vat']['glperiods_id'])) {
			$period_id = $this->_data['Vat']['glperiods_id'];
		} else {
			$current = $period->getPeriod(fix_date(date(DATE_FORMAT)));
			$period_id = $current['id'];
		}

		$invoice_options = $this->getPVAInvoices($period_id);

		$glparams = DataObjectFactory::Factory('GLParams');
		$this->view->set('gl_account', $glparams->vat_postponed_account());
		$this->view->set('invoices', $invoice_options);
		$this->view->set('periods', $period->getOpenPeriods(false));
		$this->view->set('current_period', $current['id']);
		$this->view->set('vat', DataObjectFactory::Factory('Vat'));
		$this->view->set('page_title','Import PVA Entry');
	}

	/**
	 * Return Purchase Invoices with a PVA tax status
	 * with an invoice date in the specified period
	 *
	 * @param string $period_id  GL Period ID
	 * @return mixed  Array/AJAX response
	 */
	public function getPVAInvoices($period_id='')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['period_id'])) {
				$period_id = $this->_data['period_id'];
			}
		}

		$tax_statuses = new TaxStatus();
		$pva_statuses = $tax_statuses->get_pva_statuses();

		$gl_period = DataObjectFactory::Factory('GLPeriod');
		$gl_period->load($period_id);
		
		//Get a list of invoice numbers that have PVA VAT entries
		$pva_entries = new VatPVPurchasesCollection();
		$pva_entries_search = new SearchHandler($pva_entries, false);
		$entries_cc = new ConstraintChain();
		$entries_cc->add(new Constraint('year', '=', $gl_period->year));
		$entries_cc->add(new Constraint('year', '=', $gl_period->year -1), 'OR');
		$pva_entries_search->addConstraintChain($entries_cc);
		$pva_entries->load($pva_entries_search);

		$pva_invoice_numbers = [];
		foreach ($pva_entries as $entry) {
			if (!in_array($entry->docref, $pva_invoice_numbers)) {
				$pva_invoice_numbers[] = $entry->docref;
			}
		}

		//Select invoices that require a VAT entry for PVA
		$pva_invoices = new PInvoiceCollection();
		$sh = new SearchHandler($pva_invoices, false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('tax_status_id', 'IN', '(' . implode(',', array_keys($pva_statuses)) . ')'));
		//Exclude invoices that have PVA entries
		if (count($pva_invoice_numbers) > 0 ) {
			$cc->add(new Constraint('invoice_number', 'NOT IN', '(' . implode(',', $pva_invoice_numbers) . ')'));
		}
		$cc->add(new Constraint('transaction_type', '=', 'I'));
		$cc->add(new Constraint('status', '!=', 'N'));
		$sh->addConstraintChain($cc);
		$pva_invoices->load($sh);

		//Generate invoice options
		$invoice_options = [];
		foreach ($pva_invoices as $invoice) {
			$invoice_options[$invoice->invoice_number] = "{$invoice->invoice_number} - {$invoice->supplier}";
		}

		//Return options in the appropriate format
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $invoice_options);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $invoice_options;
		}
	}

	/**
	 * Save VAT Journal or PVA Entry
	 *
	 * @return void
	 */
	public function savejournal ()
	{
		$flash = Flash::Instance();
		
		$errors = array();
		
		if (!$this->checkParams('Vat'))
		{
			sendBack();
		}
		
		$data = $this->_data['Vat'];
		
		if ($data['value']['vat']<=0)
		{
			$errors[]='Vat Value must be greater than zero';
		}
		else
		{
			$glparams = DataObjectFactory::Factory('GLParams');
			$vat_type = 'vat_'.$data['vat_type'];
			$data['vat_account'] = call_user_func(array($glparams, $vat_type));
			
			if ($data['vat_type'] == 'PVA') {
				$data['docref'] = $data['invoice'];
			}

			if ($data['vat_type']=='input' || $data['vat_type']=='PVA')
			{
				$data['value']['net'] = bcmul($data['value']['net'], -1);
				$data['value']['vat'] = bcmul($data['value']['vat'], -1);
			}
			
			$data['transaction_date'] = date(DATE_FORMAT);

			if ($data['vat_type'] == 'PVA') {
				$gltransactions = GLTransaction::makePVAEntry($data, $errors);
			} else {
				$gltransactions = GLTransaction::makeFromVATJournalEntry($data, $errors);
			}
			
			if (count($errors)==0 && GLTransaction::saveTransactions($gltransactions, $errors))
			{
				$flash->addMessage('VAT Journal created OK');
				if ($data['value']['net']<=0 && $data['vat_type'] != 'PVA') {
					$flash->addWarning('Transaction saved with Net Value of zero');
				}
				sendTo($this->name, '', $this->_modules);
			}
		}
		
		$flash->addErrors($errors);
		
		$this->_data['vat_type']=$data['vat_type'];
		$this->_data['glaccount_id']=$data['glaccount_id'];
		
		$this->refresh();
	}

	public function getCentres($_id='')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account = DataObjectFactory::Factory('GLAccount');
		$account->load($_id);
		$centres=$account->getCentres();
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $centres;
		}
	}
	
	public function viewEuArrivals ()
	{

		$collection = new VatCollection($this->_templateobject);
		$collection->setParams();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['received_date']['from'] = un_fix_date($this->_data['start_date']);
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['received_date']['to'] = un_fix_date($this->_data['end_date']);
		}
		
		$this->setSearch('VatSearch', 'Transactions', $s_data, 'received_date');
		
		$sh = $this->setSearchHandler($collection);
		
		$collection->eu_arrivals($sh);
		
		$measure_fields=array('commodity_code'=>'', 'country_code'=>'');
		
		$aggregate_fields=array('sterling_order_line_value'=>array('decimal_places'=>2),'net_mass'=>array('decimal_places'=>2));
		
		$this->setBreakLevels($measure_fields, $aggregate_fields);
		
		$this->view->set('clickaction', 'edit_received_line');
		$this->view->set('enablelink', array('net_mass'=>'net_mass'));
	
		return $this->viewEUTransactions($collection, $sh);
	}
	
	public function viewEuDespatches ()
	{
		
		$collection = new VatCollection($this->_templateobject);
		
		$collection->setParams();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['despatch_date']['from'] = un_fix_date($this->_data['start_date']);
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['despatch_date']['to'] = un_fix_date($this->_data['end_date']);
		}
	
		$this->setSearch('VatSearch', 'Transactions', $s_data, 'despatch_date');

		$sh = $this->setSearchHandler($collection);
		
		$collection->eu_despatches($sh);
		
		$measure_fields = array('commodity_code'=>'', 'country_code'=>'');
		
		$aggregate_fields = array('sterling_order_line_value'=>array('decimal_places'=>2),'net_mass'=>array('decimal_places'=>2));
		
		$this->setBreakLevels($measure_fields, $aggregate_fields);
		
		$this->view->set('clickaction', 'edit_despatch_line');
		$this->view->set('enablelink', array('net_mass'=>'net_mass'));
	
		return $this->viewEUTransactions($collection, $sh);
	}
	
	public function viewEuSalesList ()
	{
		
		$collection = new VatCollection($this->_templateobject);
		
		$collection->setParams();
		
		if (isset($this->_data['start_date']))
		{
			$s_data['invoice_date']['from'] = un_fix_date($this->_data['start_date']);
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['invoice_date']['to'] = un_fix_date($this->_data['end_date']);
		}
	
		$this->setSearch('VatSearch', 'Transactions', $s_data, 'invoice_date');
		
		$sh = $this->setSearchHandler($collection);
		
		$collection->eu_saleslist($sh);
		
		$measure_fields = array('vat_number'=>'', 'report'=>'');
		
		$aggregate_fields = array('base_tax_value'=>array('normal_enable_formatting'	=> 'true',
														'normal_decimal_places'		=> 2,
							   							'normal_justify'			=> 'right',
							   							'normal_method'				=> 'sum',
							   							'normal_total'				=> 'report')
							   ,'base_net_value'=>array('normal_enable_formatting'	=> 'true',
														'normal_decimal_places'		=> 2,
							   							'normal_justify'			=> 'right',
							   							'normal_method'				=> 'sum',
							   							'normal_total'				=> 'report'));

		$this->setBreakLevels($measure_fields, $aggregate_fields);
		$this->view->set('clickaction', 'none');
	
		return $this->viewEUTransactions($collection, $sh);
	}
	
	
	/**
	 * View the list of transactions of each type for this VAT return
	 * 
	 * Calls private function printTransactions() for printing
	 * 
	 * Exporting to csv is handled here not in the print section
	 */
	public function viewTransactions()
	{
		$errors = array();
		
		if ((isset($this->_data['year'])) && (isset($this->_data['tax_period'])))
		{
			$s_data['year']			= $this->_data['year'];
			$s_data['tax_period']	= $this->_data['tax_period'];
		}
		else
		{
			$glperiod = DataObjectFactory::Factory('GLPeriod');
			$glperiod->getCurrentTaxPeriod();
			
			if ($glperiod)
			{
				$s_data['year']			= $glperiod->year;
				$s_data['tax_period']	= $glperiod->tax_period;
			}
		}
		$this->setSearch('VatTransSearch', 'useDefault', $s_data);
		$this->search->disable_field_selection = true;

		$tax_period = $this->search->getValue('tax_period');
		$year = $this->search->getValue('year');
		
		if (isset($this->_data['box']))
		// switch the model/collection based on the 'box' (sic)
		{
			switch ($this->_data['box']) {
				case '4': // inputs
					$this->_templateobject = DataObjectFactory::Factory('VatInputs');
					$this->uses($this->_templateobject);
					$vatTransactions = new VatInputsCollection($this->_templateobject);
					$company_type='supplier';
					break;
				case '6': // outputs
					$this->_templateobject = DataObjectFactory::Factory('VatOutputs');
					$this->uses($this->_templateobject);
					$vatTransactions = new  VatOutputsCollection($this->_templateobject);
					$company_type='customer';
					break;
				case '8': // eu sales
					$this->_templateobject = DataObjectFactory::Factory('VatEuSales');
					$this->uses($this->_templateobject);
					$vatTransactions = new  VatEuSalesCollection($this->_templateobject);
					$company_type='customer';
					break;
				case '9': // eu purchases
					$this->_templateobject = DataObjectFactory::Factory('VatEuPurchases');
					$this->uses($this->_templateobject);
					$vatTransactions = new VatEuPurchasesCollection($this->_templateobject);
					$company_type='supplier';
					break;
				case '98': // postponed VAT
					$this->_templateobject = DataObjectFactory::Factory('VatPVPurchases');
					$this->uses($this->_templateobject);
					$vatTransactions = new  VatPVPurchasesCollection($this->_templateobject);
					$company_type='supplier';			
					break;					
				case '99': // reverse charge VAT
					$this->_templateobject = DataObjectFactory::Factory('VatRCPurchases');
					$this->uses($this->_templateobject);
					$vatTransactions = new VatRCPurchasesCollection($this->_templateobject);
					$company_type='supplier';
					break;
			}
			// Show the data in the index
			parent::index($vatTransactions);
			$this->view->set('box',$this->_data['box']);
			$this->view->set('page_title',"VAT Transactions {$year}/{$tax_period} - ".$this->titles[$this->_data['box']]);
		}		

		$return = new VatReturn();
		$return->loadVatReturn($year, $tax_period);

		if ($this->_data['format'] == 'csv') {

			$sh = new SearchHandler($vatTransactions, FALSE);
			$sh->addConstraintChain(new Constraint('year', '=',  $s_data['year']));
			$sh->addConstraintChain(new Constraint('tax_period', '=', $s_data['tax_period']));
			$csvExports = $vatTransactions->load($sh,'',3);

			$column_order = ['transaction_date',
                            'docref',
                            'ext_reference',
                            $company_type,
                            'comment',
                            'vat',
                            'net',
                            'source',
                            'type'];

            // output headers so that the file is downloaded rather than displayed
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=vat_transactions.csv');

            // create a file pointer connected to the output stream
            $handle = fopen('php://output', 'w');

            // output the column headings to match the index view
            fputcsv($handle, ['Date',
			'Doc Ref',
			'Ext Ref',
			'Company',
			'Comment',
			'VAT',
			'Net',
			'Source',
			'Type']);

            foreach($csvExports as $csvrow) {
                $csvrow = array_slice(array_merge(array_flip($column_order), $csvrow),0,9);
                fputcsv($handle, $csvrow);
            }
            fclose($handle);
            exit();
        }

		$sidebar = new SidebarController($this->view);

		$returns_sidebar['all'] = [
            'link' => [
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
			], 'tag' => "View All VAT Returns"
		];
		
		$returns_sidebar['viewvatreturn'] = [
			'link'=>['modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'view'
						 ,'id' => $return->id
			], 'tag'=>"View {$year}/{$tax_period} VAT Return"
		];

		$sidebar->addList('VAT Returns', $returns_sidebar);
		$sidebar->addList("{$return->year}/{$return->tax_period} Reports", $this->reportSidebar($this->titles, $return->year, $return->tax_period));
		
		$print_params = array();
		
		if (isset($this->_data['box']))
		{
			$print_params['box'] = $this->_data['box'];
			$print_params['year'] = $year;
			$print_params['tax_period'] = $tax_period;
		}

		$sidebar->addList(
			'Output',
			array(
				'printtransactions'=>array(
					'link'=>array_merge(array('modules'=>$this->_modules
											 ,'controller'=>$this->name
											 ,'action'=>'printDialog'
											 ,'printaction'=>'printTransactions'
											 ,'filename'=>'Transactions_'.$year.'-'.$tax_period
											 )
									   ,$print_params),
					'tag'=>'Print Transactions'
				),
				'exporttransactions'=>array(
					'link'=>array_merge(array('modules'=>$this->_modules
											 ,'controller'=>$this->name
											 ,'action'=>'viewTransactions'
											 ,'format'=>'csv'
											 )
									   ,$print_params),
					'tag'=>'Export Transactions to CSV'
				)
			)
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function viewDetail ()
	{
		sendTo('gltransactions'
					,'view'
					,'general_ledger'
					,array('id'=>$this->_data['id']));
	}

	public function edit_despatch_line ()
	{
		$flash=Flash::Instance();
		
		if (!$this->checkParams('id'))
		{
			sendback();
		}
		
		$transaction = DataObjectFactory::Factory('SODespatchLine');
		$transaction->load($this->_data['id']);
		
		if (!$transaction->isLoaded())
		{
			$flash->addError('Error loading Despatch Line');
			sendback();
		}
		
		$this->view->set('transaction', $transaction);
		$this->view->set('date_field', 'despatch_date');
		$this->view->set('qty_field', 'despatch_qty');
		$this->view->set('company_field', 'customer');
		
		$messages=array();
		
		$net_mass=$this->getNetMass ($transaction->stitem_id, $transaction->stuom_id, $transaction->despatch_qty, $messages);
		
		if (count($messages)>0)
		{
			$flash->addWarnings($messages);
		}
		
		$this->view->set('net_mass', $net_mass);
		
		$this->setTemplateName('editeutransactions');
	}
	
	public function edit_received_line ()
	{
		$flash=Flash::Instance();
		
		if (!$this->checkParams('id'))
		{
			sendback();
		}
		
		$transaction = DataObjectFactory::Factory('POReceivedLine');
		$transaction->load($this->_data['id']);
		
		if (!$transaction->isLoaded())
		{
			$flash->addError('Error loading Received Line');
			sendback();
		}
		
		$this->view->set('transaction', $transaction);
		$this->view->set('date_field', 'received_date');
		$this->view->set('qty_field', 'received_qty');
		$this->view->set('company_field', 'supplier');
		
		$messages=array();
		
		$net_mass=$this->getNetMass ($transaction->stitem_id, $transaction->stuom_id, $transaction->received_qty, $messages);
		
		if (count($messages)>0)
		{
			$flash->addWarnings($messages);
		}
		
		$this->view->set('net_mass', $net_mass);
		
		$this->setTemplateName('editeutransactions');
	}

	public function savetransaction ()
	{
		$flash=Flash::Instance();
		
		if (!$this->checkParams('model_type'))
		{
			$flash->addError('model_type not defined');
			sendback();
		}
		
		if (!$this->checkParams($this->_data['model_type']))
		{
			$flash->addError('No input data for '.$this->_data['model_type']);
			sendback();
		}
		
		$model=$this->_data['model_type'];
		
		if (empty($this->_data[$model]['id']) || empty($this->_data[$model]['net_mass']))
		{
			$flash->addError('No id or Net Mass value for '.$model);
			sendback();
		}
		
		$transaction = DataObjectFactory::Factory($model);
		
		if ($transaction->netMass<>$this->_data[$model]['net_mass'])
		{
			if (!$transaction->update($this->_data[$model]['id'], 'net_mass', $this->_data[$model]['net_mass']))
			{
				$flash->addError('Error updating '.$model.' Net Mass value');
			}
			else
			{
				$flash->addMessage($model.' Net Mass value updated');
			}
		}
		
		sendTo($_SESSION['refererPage']['controller']
			  ,$_SESSION['refererPage']['action']
			  ,$_SESSION['refererPage']['modules']
			  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		
	}
	
// Private functions
	private function viewEuTransactions ($collection, $sh)
	{

		$errors = array();
		$s_data = array();
		
		$flash = Flash::Instance();
				
		$this->view->set('page_title', $collection->title);

		if (isset($this->search))
		{
			if ($this->isPrintDialog())
			{
				$_SESSION['printing'][$this->_data['index_key']]['search_id']=$sh->search_id;
				return $this->printCollection();
			}
			elseif ($this->isPrinting())
			{
				$_SESSION['printing'][$this->_data['index_key']]['search_id']=$sh->search_id;
				$sh->setLimit(0);
				$collection->load($sh);
//				$this->setParams();
				$this->printCollection($collection);
				exit;
			}
		} 
	
		parent::index($collection, $sh);

		$this->view->set('transactions', $collection);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList('VAT Returns', $this->vatReturnSidebar());
		$sidebar->addList('Intrastat', $this->intrastatSidebar());
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('display_fields','');
		$this->view->set('selected_fields','');
		
		$this->setTemplateName('vieweutransactions');
	}
	
	private function getNetMass ($_stitem_id, $_stuom_id, $qty, &$messages)
	{
		$stitem = DataObjectFactory::Factory('STItem');
		
		$stitem->load($_stitem_id);
		
		$param = DataObjectFactory::Factory('GLParams');
		
		$net_mass_uom_id=$param->getParam($param->intrastat_net_mass);
		
		if ($stitem->isLoaded() && !empty($net_mass_uom_id))
		{
			$net_mass=$stitem->convertToUoM($_stuom_id, $net_mass_uom_id, $qty);
		}
		
		if (empty($net_mass) || $net_mass===false)
		{
			$messages[]='No conversion factor for this item/uom';
			$net_mass=0;
		}
		return $net_mass;
	}
	
	private function reportSidebar($titles, $year, $tax_period)
	{
		$list = array(
			'box4'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>4
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[4]
			),
			'box6'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>6
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[6]
			),
			'box8'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>8
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[8]
			),
			'box9'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>9
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[9]
			),
			'box98'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>98
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[98]
			),
			'box99'=>array(
				'link'=>array('modules'=>$this->_modules
								,'controller'=>$this->name
								,'action'=>'viewTransactions'
								,'box'=>99
								,'year'=>$year
								,'tax_period'=>$tax_period
								),
				'tag'=>$titles[99]
			)
		);
		return $list;
	}
	
	private function intrastatSidebar ()
	{
		$sidebarlist=array();

		$sidebarlist['arrivals'] = array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'viewEuArrivals'
								 ),
					'tag'=>'View EU Arrivals'
		);
		
		$sidebarlist['despatches'] = array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'viewEuDespatches'
								 ),
					'tag'=>'View EU Despatches'
		);
				
		$sidebarlist['saleslist'] = array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'viewEuSalesList'
								 ),
					'tag'=>'View EU Sales List'
				);
				
		return $sidebarlist;
		
	}

	private function vatReturnSidebar ()
	{
		$sidebarlist=array();
		$sidebarlist['vatreturn'] = array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ),
					'tag'=>'View All VAT Returns'
				);
				
		return $sidebarlist;
		
	}

	public function CloseVatPeriod() {
		$this->checkRequest(['post'], true);
		if (! $this->checkParams('id')) {
            sendBack();
		}
		
		$flash = Flash::Instance();
		$errors		= array();
				
		// load the model
		$return = new VatReturn;
		$return->load($this->_data['id']);
		$return->getTaxPeriodStatus();
		
		$vat = new Vat;
		$vat->vatreturn($return->tax_period, $return->year, $errors);
		if ($return->tax_period_closed === 'f' && $return->gl_period_closed === 't')
		{
			$result = $vat->closePeriod($return->tax_period, $return->year, $errors);
			if (count($errors) > 0)
			{
				$flash->addErrors($errors);
				sendBack();
			}
			$flash->addMessage("VAT Period {$return->year}/{$return->tax_period} Closed");
		} else if ($return->tax_period_closed === 't') {
			$flash->addError('VAT Period already closed');
		} else {
			$flash->addError('GL Periods open, unable to close VAT Period');
		}
		sendBack();
	}
	
	/* output functions */
	public function printVatReturn($status = 'generate')
	{
		
		// build options array
		$options = array(
			'type' => array(
				'pdf'	=> '',
				'xml'	=> ''
			),
			'output' => array(
				'print'	=> '',
				'save'	=> '',
				'email'	=> '',
				'view'	=> ''
			),
			'filename'	=> 'VatReturn_' . fix_date(date(DATE_FORMAT)),
			'report'	=> 'VatReturn'
		);

		// simply return the options if we're only at the dialog stage
		if (strtolower($status) === "dialog") {
			return $options;
		};
		
		$errors		= array();
		$messages 	= array();

		$return = new VatReturn;
		$return->load($this->_data['id']);
		$return->getTaxPeriodStatus($return->tax_period, $return->year);
		
		if ($this->_data['filename'] === 'VAT_Return') {
			$this->_data['filename'] .= '_' . $return->year . '_' . $return->tax_period;
		}
		
		
		if (count($errors) > 0)
		{
			echo $this->build_print_dialog_response(
				FALSE,
				array('message'=>implode('<br />', $errors))
			);
			exit;
		}

		$extra['boxes'][]['line'][] = [
			'title' => 'VAT due on sales and other outputs.',
			'box_num' => 'Box 1',
			'value' => $return->vat_due_sales,
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'VAT due on acquisitions from other EC Member States.',
			'box_num' => 'Box 2',
			'value' => $return->vat_due_acquisitions,
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Total VAT due',
			'box_num' => 'Box 3',
			'value' => $return->total_vat_due,
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'VAT reclaimed on purchases and other inputs (including acquisitions from the EC). ',
			'box_num' => 'Box 4',
			'value' => $return->vat_reclaimed_curr_period,
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Net VAT Due',
			'box_num' => 'Box 5',
			'value' => $return->net_vat_due,
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Total value of sales and all other outputs excluding any VAT.',
			'box_num' => 'Box 6',
			'value' => round($return->total_value_sales_ex_vat),
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Total value of purchases and all other inputs excluding any VAT (including exempt purchases)',
			'box_num' => 'Box 7',
			'value' => round($return->total_value_purchase_ex_vat),
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Total value of all supplies of goods and related costs, excluding any VAT, to other EC member states. ',
			'box_num' => 'Box 8',
			'value' => round($return->total_value_goods_supplied_ex_vat),
		];

		$extra['boxes'][]['line'][] = [
			'title' => 'Total value of acquisitions of goods and related costs excluding any VAT, from other EC member states.',
			'box_num' => 'Box 9',
			'value' => round($return->total_acquisitions_ex_vat),
		];

		if ($return->finalised === 't')
		{
			$extra['submission'] = [
				'processing_date' => $return->processing_date,
				'form_bundle' => $return->form_bundle,
				'charge_ref_number' => $return->charge_ref_number,
				'receipt_id_header' => $return->receipt_id_header,
			];
		} else {
			$extra['mtd_not_submitted'] = true;
		}
		
		if ($return->tax_period_closed === 'f')
		{
			$extra['tax_period_not_closed'] = true;
		}
		
		$extra['title'] = 'VAT Return ' . $return->year . '-' . $return->tax_period;
		
		// generate the xml and add it to the options array
		$options['xmlSource'] = $this->generateXML(array('extra'=>$extra));
		
		// execute the print output function, echo the returned json for jquery
		echo $this->generate_output($this->_data['print'], $options);
		exit;
		
	}
	
	public function printTransactions($status = 'generate')
	{
		$errors = [];
		// build options array
		$options = array(
			'type' => array(
				'pdf' => '',
				'xml' => ''
			),
			'output' => array(
				'print'	=> '',
				'save'	=> '',
				'email'	=> '',
				'view'	=> ''
			),
			'filename'	=> 'VatTransactions'.fix_date(date(DATE_FORMAT)),
			'report'	=> 'VatTransaction'
		);

		// simply return the options if we're only at the dialog stage
		if (strtolower($status) === "dialog")
		{
			return $options;
		}

		$this->setSearch('VatSearch', 'useDefault', array());

		$tax_period	= $this->_data['tax_period'];
		$year		= $this->_data['year'];

		$cc = new ConstraintChain();
		$cc->add(new Constraint('tax_period', '=', $tax_period));
		$cc->add(new Constraint('year', '=', $year));

		if (isset($this->_data['box']))
		{
			switch ($this->_data['box']) {
				case '4': // inputs
					$gltransaction = DataObjectFactory::Factory('VatInputs');
					$gltransactions = new VatInputsCollection($gltransaction);
					break;
				case '6': // outputs
					$gltransaction = DataObjectFactory::Factory('VatOutputs');
					$gltransactions = new VatOutputsCollection($gltransaction);
					break;
				case '8': // eu sales
					$gltransaction = DataObjectFactory::Factory('VatEuSales');
					$gltransactions = new VatEuSalesCollection($gltransaction);
					break;
				case '9': // eu purchases
					$gltransaction = DataObjectFactory::Factory('VatEuPurchases');
					$gltransactions = new VatEuPurchasesCollection($gltransaction);
					break;
				case '98': // postponed VAT
					$gltransaction = DataObjectFactory::Factory('VatPVPurchases');
					$gltransactions = new VatPVPurchasesCollection($gltransaction);
					break;					
				case '99': // reverse charge VAT
					$gltransaction = DataObjectFactory::Factory('VatRCPurchases');
					$gltransactions = new VatRCPurchasesCollection($gltransaction);
					break;	
			}
		}

		$sh = new SearchHandler($gltransactions, false);
		$sh->addConstraint($cc);
		$gltransactions->load($sh);
		// Need the underling view here so we can get the sum
		$viewname=$gltransactions->_tablename;
		$totalvat = $this->_templateobject->getSum('vat', $cc, $viewname);
		$totalnet = $this->_templateobject->getSum('net', $cc, $viewname);

		if (count($errors) > 0)
		{
			echo $this->build_print_dialog_response(
				FALSE,
				array('message'=>implode('<br />', $errors))
			);
			exit;
		}
		
		if (isset($this->_data['box']))
		{
			set_time_limit(180);
			if ($gltransactions === false)
			{
				echo $this->build_print_dialog_response(
					FALSE,
					array('message'=>'Not all control accounts have been assigned')
				);
				exit;
			}
		}
		else
		{
			echo $this->build_print_dialog_response(
				FALSE,
				array('message'=>'No box selected')
			);
			exit;
		}

		$title = 'Audit Trail : Year ' . $year . ' - Tax Period ' . $tax_period;
		
		switch ($this->_data['box'])
		{
			case 1:
			case 6:
				$title = "VAT Outputs {$title}";
				break;
			case 4:
			case 7:
				$title = "VAT Inputs {$title}";
				break;
			case 8:
				$title = "VAT EU Sales {$title}";
				break;
			case 2:
			case 9:
				$title = "VAT EU Purchases {$title}";
				break;
			case 98:
				$title = "Postponed VAT Purchases {$title}";
				break;
			case 99:
				$title = "Reverse Charge Purchases {$title}";
				break;
		}
		
		$extra = array(
			'title'		=> $title
			,'totalvat' => number_format($totalvat,2)
			,'totalnet' => number_format($totalnet,2)

		);
					
		// generate the xml and add it to the options array
		$options['xmlSource'] = $this->generate_xml(
			array(
				'model'					=> $gltransactions,
				'extra'					=> $extra,
				'load_relationships'	=> FALSE
			)
		);

		$options['query'] = $gltransactions->query;
		
		echo $this->generate_output($this->_data['print'], $options);
		exit;
		
	}

	/**
	 * Submit return to HMRC
	 */
	public function hmrcPostVat()
	{
		$this->checkRequest(['post'], true);
		if (! $this->checkParams('id')) {
            sendBack();
        }

		$vat_return = new VatReturn();
		$vat_return->load($this->_data['id']);
		$year = $vat_return->year;
		$tax_period = $vat_return->tax_period;

		$mtd = new MTD($this->_data['fp']);
		$success = $mtd->postVat($year, $tax_period);
		
		sendBack();
	}

	/**
	 * Calculate and update current VAT position
	 */
	public function calculateVAT()
	{
		$this->checkRequest(['post'], true);
		if (! $this->checkParams('id')) {
            sendBack();
		}
		
		$flash = Flash::Instance();
		$errors		= array();
		$messages 	= array();

		$vat_return = new VatReturn();
		$vat_return->load($this->_data['id']);
		$year = $vat_return->year;
		$tax_period = $vat_return->tax_period;

		$vat = new Vat();
		$vat->vatreturn($tax_period, $year, $errors);
		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
			return false;
		}

		if ($vat->tax_period_closed === 't') {
			$flash->addError('Tax period is closed');
			sendBack();
		}

		$boxes = $vat->getVATvalues($year, $tax_period);
		try
		{
			$return = new VatReturn();
			$return->updateVatReturnBoxes($year, $tax_period, $boxes);
		}
		catch (VatReturnStorageException $e)
		{
			$flash->addError($e->getMessage());
		}
		sendBack();
	}

	public function view() {
		$flash = Flash::Instance();
		$mtd_config = OauthStorage::getconfig('mtd-vat');
		if ($mtd_config === null) {
			$flash->addWarning('Making Tax Digital for VAT is not configured');
			$mtd_configured = false;
			$mtd_authorised = false;
		} else {
			$mtd_configured = true;
			$mtd = new MTD();
			$result = $mtd->refreshToken();
			if ($result === true) {
				$mtd_authorised = true;
			}
		}

        $flash = Flash::Instance();
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $model = $this->_uses[$this->modeltype];
		$this->view->set('model', $model);
		
		$model->getTaxPeriodStatus ($model->tax_period, $model->year);
		$this->view->set('tax_period_closed', $model->tax_period_closed);

		$sidebar = new SidebarController($this->view);

        $sidebarlist = array();

		if ($model->tax_period_closed !== 't') {
			$sidebarlist['updatevatposition'] = array(
				'link'=>array('modules'=>$this->_modules
							,'controller'=>$this->name
							,'action'=>'calculateVAT'
							),
				'tag'=>'Update VAT Postion',
				'class' => 'vat-confirm',
				'data_attr' => [
					'data_uz-confirm-message' => "Recalculate VAT?|This cannot be undone.",
					'data_uz-action-id' => $model->id
				]
			);
		}

		if ($model->tax_period_closed !== 't' && $model->gl_period_closed === 't') {
			$sidebarlist['closevatperiod'] = array(
				'link'=>array('modules'=>$this->_modules
							,'controller'=>$this->name
							,'action'=>'closeVatPeriod'
							),
				'tag'=>'Close VAT Period',
				'class' => 'vat-confirm',
				'data_attr' => [
					'data_uz-confirm-message' => "Close VAT Period?|This cannot be undone.",
					'data_uz-action-id' => $model->id
				]
			);
		}

		if ($model->tax_period_closed === 't' && $model->finalised === 'f' && $mtd_configured === true && $mtd_authorised === true) {
			$sidebarlist['submitreturn'] = array(
				'link'=>array('modules'=>$this->_modules
							,'controller'=>$this->name
							,'action'=>'hmrcPostVat'
							),
				'tag'=>'Submit VAT Return',
				'class' => 'vat-confirm',
				'data_attr' => [
					'data_uz-confirm-message' => "Submit VAT Return to HMRC?|When you submit this VAT information you are making a legal declaration that the information is true and complete. A false declaration can result in prosecution.",
					'data_uz-action-id' => $model->id
				]
			);
		}

		$sidebarlist['printvatreturn'] = array(
			'link'=>array('modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'printDialog'
						 ,'id' => $model->id
						 ,'printaction'=>'printVatReturn'
						 ,'filename'=>'VAT_Return'
						 ),
			'tag'=>'Print VAT Return'
		);

		$sidebarlist['viewtransactions'] = array(
			'link'=>array('modules'=>$this->_modules
						 ,'controller'=>$this->name
						 ,'action'=>'viewTransactions'
						 ,'box'=>4
						 ,'year'=>$model->year
						 ,'tax_period'=>$model->tax_period
						 ),
			'tag'=>'View Transactions'
		);

		$returns_sidebar['all'] = [
            'link' => [
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
			], 'tag' => "View All VAT Returns"
		];

		$sidebar->addList('VAT Returns', $returns_sidebar);
		$sidebar->addList("{$model->year}/{$model->tax_period} Actions", $sidebarlist);

		$this->sidebarRelatedItems($sidebar, $model);
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
		$this->view->set('page_title',"Vat Return {$model->year}/{$model->tax_period}");
	}

	/**
	 * This action is for testing purposes
	 * 
	 * Get VAT Obligations from HMRC and dump to the browser
	 * 
	 * URI: /?module=vat&controller=vat&action=vatObligations
	 */
	public function vatObligations() {
		$mtd = new MTD;
		var_dump($mtd->getObligations(['status' => 'O']));
		exit;
  	}
}
// End of VatController
