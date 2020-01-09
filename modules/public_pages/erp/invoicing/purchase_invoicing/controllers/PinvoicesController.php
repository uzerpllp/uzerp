<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PinvoicesController extends printController {

	protected $version='$Revision: 1.57 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->uses(DataObjectFactory::Factory('PInvoiceLine'), FALSE);
		$this->_templateobject = DataObjectFactory::Factory('PInvoice');
		$this->uses($this->_templateobject);
	}

	public function index(){
		$this->view->set('clickaction', 'view');
		$errors=array();
	
		$s_data=array();

// Set context from calling module
		if (isset($this->_data['plmaster_id'])) {
			$s_data['plmaster_id']=$this->_data['plmaster_id'];
		}
		if (isset($this->_data['status'])) {
			$s_data['status']=$this->_data['status'];
		}
		if (isset($this->_data['purchase_order_number'])) {
			$s_data['purchase_order_number']=$this->_data['purchase_order_number'];
		}
		
		$this->setSearch('pinvoicesSearch', 'useDefault', $s_data);

		parent::index(new PInvoiceCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$actions = array();
		
		foreach ($this->_templateobject->getEnumOptions('transaction_type') as $key=>$description)
		{
			$actions['new'.$description]=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ,'transaction_type'=>$key
								 ),
					'tag'=>'new '.$description
			);
		}
		
		$actions['printinvoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'selectinvoices'
								 ),
					'tag'=>'post invoices'
				);
		
		$actions['invoice_from_grn']=array(
					'link'=>array('module'=>'purchase_order'
								 ,'controller'=>'porders'
								 ,'action'=>'createinvoice'
								 ),
					'tag'=>'create_invoice_from_GRN'
				);
		
		$sidebar->addList('Actions', $actions);
		
		$reports = array();
		
		$reports['newinvoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 , 'action'=>'printDialog'
								 , 'printaction'=>'printInvoicelist'
								 , 'filename'=>'PInvoices'.fix_date(date(DATE_FORMAT))
								 , 'type'=>'New'
								 ),
					'tag'=>'Unposted Invoices'
				);
		
		$reports['queryinvoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'printDialog'
								 ,'printaction'=>'printInvoicelist'
								 ,'filename'=>'PInvoices'.fix_date(date(DATE_FORMAT))
								 ,'type'=>'Query'
								 ),
					'tag'=>'Query Invoices'
				);
		
		$reports['alloverdueinvoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'printDialog'
								 ,'printaction'=>'printInvoicelist'
								 ,'filename'=>'PInvoices'.fix_date(date(DATE_FORMAT))
								 ,'type'=>'Overdue'
								 ),
					'tag'=>'All Overdue invoices'
				);
		
		$reports['overdueinvoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'printDialog'
								 ,'printaction'=>'printInvoicelist'
								 ,'filename'=>'PInvoices'.fix_date(date(DATE_FORMAT))
								 ,'type'=>'Overdue'
								 ,'status'=>'O'
								 ),
					'tag'=>'Overdue invoices Not in Query'
				);
		
		$reports['daybook']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'printDialog'
								 ,'printaction'=>'printInvoicelist'
								 ,'filename'=>'PInvoices'.fix_date(date(DATE_FORMAT))
								 ,'type'=>'Day Book'
								 ),
					'tag'=>'Day Book (uses current Search Settings)'
				);
		
		$sidebar->addList('Reports', $reports);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function clone_invoice(){
		$flash = Flash::Instance();
		
		$errors = array();
		
		if (!isset($this->_data) || !$this->loadData()) {
			$this->dataError();
			sendBack();
		}	
		
		$invoice = $this->_uses[$this->modeltype];
		
		if (!$invoice->isLoaded())
		{
			$flash->addError('Error loading invoice details');
			sendBack();
		}
		
		$data[$this->modeltype] = array();
		
		foreach ($invoice->getFields() as $fieldname=>$field)
		{
			switch ($fieldname)
			{
				case $invoice->idField:
				case 'created':
				case 'createdby':
				case 'lastupdated':
				case 'alteredby':
				case 'invoice_number':
				case 'invoice_date':
				case 'original_due_date':
				case 'auth_date':
				case 'auth_by':
					break;
				case 'due_date':
					$data[$this->modeltype][$fieldname] = un_fix_date($invoice->$fieldname);
					break;
				case 'status':
					$data[$this->modeltype][$fieldname] = $invoice->newStatus();
					break;
				default:
					$data[$this->modeltype][$fieldname] = $invoice->$fieldname;
			}
		}
		
		if (!empty($this->_data['transaction_type']))
		{
			$data[$this->modeltype]['transaction_type'] = $this->_data['transaction_type'];
		}
		
		$line_count = 0;
		
		foreach ($invoice->lines as $invoiceline)
		{
			$modelname = get_class($invoiceline);
			foreach ($invoiceline->getFields() as $fieldname=>$field)
			{
				switch ($fieldname)
				{
					case $invoiceline->idField:
					case 'created':
					case 'createdby':
					case 'lastupdated':
					case 'alteredby':
					case 'delivery_note':
						break;
					case 'purchase_order_id':
					case 'order_line_id':
						if ($this->_data['transaction_type'] == 'C')
						{
							$data[$modelname][$fieldname][$line_count] = $invoiceline->$fieldname;
						}
						break;
					case 'invoice_id':
						$data[$modelname][$fieldname][$line_count] = '';
						break;
					case 'invoice_line_id':
						if ($this->_data['transaction_type'] == 'C')
						{
							$data[$modelname][$fieldname][$line_count] = $invoiceline->{$invoiceline->idField};
						}
						break;
					case 'productline_id':
						if (!is_null($invoiceline->productline_id))
						{
							$productline = DataObjectFactory::Factory('POProductLine');
							$productline->load($invoiceline->productline_id);
							if (!$productline->isLoaded() || (!is_null($productline->end_date) && $productline->end_date<un_fix_date(date(DATE_FORMAT))))
							{
								$flash->addWarning('Selected Product is no longer valid on line '.$invoiceline->line_number);
								$invoiceline->description .= ' ** Selected Product is no longer valid';
								$data[$modelname]['description'][$line_count] .= ' ** Selected Product is no longer valid';
							}
						}
					default:
						$data[$modelname][$fieldname][$line_count] = $invoiceline->$fieldname;
				}
			}
			$line_count++;
		}
		
		$result = $invoice->save_model($data);
		if ($result!==FALSE)
		{
			sendTo($this->name, 'view', $this->_modules, array($invoice->idField=>$result['internal_id']));
		}
		
		sendBack();
		
	}

	public function view()
	{

		if (!$this->loadData()
		&& !isset($this->_data['order_number'])
		&& !isset($this->_data['invoice_number']))
		{
			$this->dataError();
			sendBack();
		}
		
		$invoice=$this->_uses[$this->modeltype];
		
		if (!$invoice->isLoaded())
		{
			if (isset($this->_data['order_number']))
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('purchase_order_number','=',$this->_data['order_number']));
				$invoice->loadBy($cc);
			}
			elseif (isset($this->_data['invoice_number']))
			{
				$cc = new ConstraintChain();
				$cc->add(new Constraint('invoice_number','=',$this->_data['invoice_number']));
				$invoice->loadBy($cc);
			}
			
			if (!$invoice->isLoaded())
			{
				$flash->addError('Failed to find invoice');
				sendBack();
			}
		}
		
		$transaction_type_desc = $invoice->getFormatted('transaction_type');
		
		$this->view->set('transaction_type_desc', $transaction_type_desc);
		
		$invoice->setTitle($transaction_type_desc);
		
		$porders = $invoice->getOrderNumbers();

		$this->view->set('porders', $porders);
		
		$id = $invoice->id;

		$sidebar = new SidebarController($this->view);
		
		$actions = array();
		
		$actions['allsupplier'] = array(
					'link'	=> array('module'		=> 'purchase_ledger'
									,'controller'	=> 'PLSuppliers'
									,'action'		=> 'index'
									),
					'tag'	=> 'view all suppliers'
				);
		
		$actions['allInvoices'] = array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'index'
									),
					'tag'	=> 'view all invoices'
				);
		
		foreach ($invoice->getEnumOptions('transaction_type') as $key=>$description)
		{
			$actions['new'.$description] = array(
					'link'	=> array('modules'			=> $this->_modules
									,'controller'		=> $this->name
									,'action'			=> 'new'
									,'transaction_type'	=> $key
									),
					'tag'	=> 'new '.$description
			);
		}
		
		$actions['invoice_from_grn']=array(
					'link'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'porders'
									,'action'		=> 'createinvoice'
									),
					'tag'	=> 'create_invoice_from_GRN'
				);
		
		$sidebar->addList(
			'Actions',
			$actions
		);
				
		$actions = array();
		
		$actions['supplierInvoices'] = array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'index'
									,'plmaster_id'	=> $invoice->plmaster_id
									),
					'tag'	=> 'view supplier invoices'
				);
		
		$actions['supplierOrders'] = array(
					'link'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'porders'
									,'action'		=> 'index'
									,'plmaster_id'	=> $invoice->plmaster_id
									),
					'tag'	=> 'view supplier orders'
				);
		
		foreach ($invoice->getEnumOptions('transaction_type') as $key=>$description)
		{
			if($key=='C' || empty($porders))
			{
				$actions['new'.$description] = array(
					'link'	=> array('modules'			=> $this->_modules
									,'controller'		=> $this->name
									,'action'			=> 'new'
									,'plmaster_id'		=> $invoice->plmaster_id
									,'transaction_type'	=> $key
									),
					'tag'	=> 'new '.$description
				);
			}
		}
		
		$actions['invoice_from_grn'] = array(
					'link'	=> array('module'		=> 'purchase_order'
									,'controller'	=> 'porders'
									,'action'		=> 'createinvoice'
									,'plmaster_id'	=> $invoice->plmaster_id
									),
					'tag'	=> 'create_invoice_from_GRN'
				);
		
		$sidebar->addList(
			$invoice->supplier,
			$actions
		);
				
		$actions = array();
		
		foreach ($invoice->getEnumOptions('transaction_type') as $key=>$description)
		{
			if($key=='C' || empty($porders))
			{
				$actions['clone'.$description] = array(
					'link'	=> array('modules'			=> $this->_modules
									,'controller'		=> $this->name
									,'action'			=> 'clone_invoice'
									,'id'				=> $id
									,'transaction_type'	=> $key
									),
					'tag'	=> 'Save as new '.$description
				);
			}
		}
		
		if ($invoice->status == 'N')
		{
			$actions['edit'] = array(
						'link'	=> array('modules'		=> $this->_modules
										,'controller'	=> $this->name
										,'action'		=> 'edit'
										,'id'			=> $id
										),
						'tag'	=> 'Edit'
					);
			$actions['add_lines'] = array(
						'link'	=> array('modules'		=> $this->_modules
										,'controller'	=> 'pinvoicelines'
										,'action'		=> 'new'
										,'invoice_id'	=> $id
										),
						'tag'	=> 'add_lines'
					);
		}
		
		if($invoice->onQuery())
		{
			$actions['query'] = array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'toggleQueryStatus'
									,'id'			=> $id
									),
					'tag'	=> 'Take '.$invoice->getFormatted('transaction_type').' off Query'
				);
		}
		
		if($invoice->hasBeenPostednotPaid())
		{
			$actions['changeduedate'] = array(
						'link'	=> array('modules'		=> $this->_modules
										,'controller'	=> $this->name
										,'action'		=> 'change_due_date'
										,'id'			=> $id
										),
						'tag'	=> 'Change Due Date'
					);
			$actions['query'] = array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'toggleQueryStatus'
									,'id'			=> $id
									),
					'tag'	=> 'Put '.$invoice->getFormatted('transaction_type').' on Query'
				);
		}
		
		if(!$invoice->hasBeenPosted() && $invoice->lines->count() > 0 && $invoice->transaction_type != 'T')
		{
			$actions['post'] = array(
					'link'	=> array('modules'		=> $this->_modules
									,'controller'	=> $this->name
									,'action'		=> 'postinvoice'
									,'id'			=> $id
									),
					'tag'	=> 'post '.$invoice->getFormatted('transaction_type')
				);
		}
							
		if($invoice->status != 'N')
		{
			$actions['viewGLtransaction'] = array(
					'link'	=> array('module'		=> 'general_ledger'
									,'controller'	=> 'gltransactions'
									,'action'		=> 'index'
									,'docref'		=> $invoice->invoice_number
									,'source'		=> 'P'
									,'type'			=> 'I'
									),
					'tag'	=> 'View GL transaction'
				);
		}			
		
		if ($invoice->grn_lines->count()>0)
		{
				$actions['viewgrn'] = array(
					'link'	=> array('module'			=> 'goodsreceived'
									,'controller'		=> 'poreceivedlines'
									,'action'			=> 'index'
									,'invoice_number'	=> $invoice->invoice_number
									),
					'tag'	=> 'View Goods Received Note'
				);
		}
		
		$sidebar->addList(
			'This Invoice',
			$actions
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new()
	{
		parent::_new();
		
		$pinvoice = $this->_uses[$this->modeltype];
		
// get supplier list
		if ($pinvoice->isLoaded() && $pinvoice->net_value!=0)
		{
			$suppliers = array($pinvoice->plmaster_id=>$pinvoice->supplier);
				
			if ($this->settlement_discount == 0 && $pinvoice->transaction_type == 'I')
			{
				$pinvoice->settlement_discount = $pinvoice->getSettlementDiscount();
			}
		}
		else
		{
			$suppliers = $this->getOptions($this->_templateobject, 'plmaster_id', 'getOptions', 'getOptions', array('use_collection'=>true));
			
			if (!$pinvoice->isLoaded())
			{
				if (isset($this->_data['transaction_type']))
				{
					$pinvoice->transaction_type = $this->_data['transaction_type'];
				}
				else
				{
					$pinvoice->transaction_type = 'I';
				}
			}
		}
		
		if (!is_null($pinvoice->transaction_type))
		{
			$transaction_type_desc = $pinvoice->getFormatted('transaction_type');
			$this->view->set('transaction_type_desc', $transaction_type_desc);
		}
		
		$this->_templateobject->setTitle('Purchase '.$transaction_type_desc);
		
// get the default/current selected supplier
		if (isset($this->_data['plmaster_id']))
		{
		// this is set if there has been error and we are redisplaying the screen
			$defaultsupplier=$this->_data['plmaster_id'];
		}
		else
		{
			if (!$pinvoice->isLoaded())
			{
				$defaultsupplier=$this->getDefaultValue($this->modeltype, 'plmaster_id', '');
			}
			else
			{
				$defaultsupplier=$pinvoice->plmaster_id;
			}
		}
		
		if (empty($defaultsupplier))
		{
			$defaultsupplier=key($suppliers);
		}
		
		if (!$pinvoice->isLoaded())
		{
			$pinvoice->plmaster_id=$defaultsupplier;
		}
		
		$this->view->set('selected_supplier', $defaultsupplier);
		
		// get Purchase Invoice Notes for default customer or first in customer
		$this->getNotes($defaultsupplier);
		
		// This bit allows for projects and tasks to be linked 
		if (!$pinvoice->isLoaded() && !empty($this->_data['project_id']))
		{
			$pinvoice->project_id = $this->_data['project_id'];
		}
		
		// We only want non-archived projects
        $projects = Project::getLiveProjects();
        $this->view->set('projects', $projects);

        // Now get tasks for the selected project
		$this->view->set('tasks', $this->getTaskList($pinvoice->project_id));
			
	}
	
	public function delete(){
		$flash = Flash::Instance();
		parent::delete('PInvoice');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function change_due_date () {
		$pinvoice=$this->_uses['PInvoice'];
		$pinvoice->load($this->_data['id']);
		$this->view->set('pinvoice',$pinvoice);
	}

	public function saveduedate() {
// Very similar to toggleQueryStatus, could combine in single function?
		if (isset($this->_data['PInvoice'])) {
			$flash=Flash::Instance();
			$errors=array();
			$db=DB::Instance();
			$db->StartTrans();
			
			$data=$this->_data['PInvoice'];
			$pinvoice=DataObject::Factory($data, $errors, 'PInvoice');
			if ($pinvoice && count($errors)==0 && $pinvoice->save()) {
				$due_date=fix_date($data['due_date']);
				$pltrans=DataObjectFactory::Factory('PLTransaction');
				$cc=new ConstraintChain();
				$cc->add(new Constraint('transaction_type', '=', $pinvoice->transaction_type));
				$cc->add(new Constraint('our_reference', '=', $data['invoice_number']));
				$pltrans->loadBy($cc);
				if ($pltrans->isLoaded()) {
					if (!$pltrans->update($pltrans->id, 'due_date', $due_date)) {
						$errors[]='Failed to update Ledger transaction';
					}
				} else {
					$errors[]='Cannot find Ledger transaction';
				}
			} else {
				$errors[]='Failed to update Invoice';
			}

			if (count($errors)==0 && $db->CompleteTrans()) {
				$flash->addMessage('Due Date changed');
			} else {
				$flash->addErrors($errors);
				$flash->addError('Failed to change Due Date');
				$db->FailTrans();
			}
			sendTo($this->name,'view',$this->_modules,array('id'=>$data['id']));
		}
		sendTo($this->name,'index',$this->_modules);
	}
	
	public function toggleQueryStatus() {

		$flash = Flash::Instance();
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$pinvoice=$this->_uses[$this->modeltype];
		if (!$pinvoice->isLoaded()) {
			$flash->addError('Failed to find invoice');
			sendBack();
		}
		
		$id = $this->_data['id'];
		
		$db=DB::Instance();
		$db->StartTrans();
		$errors=array();
		
		if ($pinvoice->status=='Q') {
			$pinvoice->status='O';
			$status='taking '.$pinvoice->getFormatted('transaction_type').' off query';
		} else {
			$pinvoice->status='Q';
			$status='putting '.$pinvoice->getFormatted('transaction_type').' on query';
		}
		if (!$pinvoice->save()) {
			$errors[]='Failed to amend '.$pinvoice->getFormatted('transaction_type');
		}
		
		$pltrans=DataObjectFactory::Factory('PLTransaction');
		$cc=new ConstraintChain();
		$cc->add(new Constraint('transaction_type', '=', $pinvoice->transaction_type));
		$cc->add(new Constraint('our_reference', '=', $pinvoice->invoice_number));
		$pltrans->loadBy($cc);
		if ($pltrans) {
			if (!$pltrans->update($pltrans->id, 'status', $pinvoice->status)) {
				$errors[]='Failed to update Ledger transaction';
			}
		} else {
			$errors[]='Cannot find Ledger transaction';
		}
		if (count($errors)==0) {
			$flash->addMessage('Succeeded in '.$status);
		} else {
			$flash->addErrors($errors);
			$flash->addError('Failed in '.$status);
			$db->FailTrans();
		}
		$db->CompleteTrans();
		sendTo($this->name,'view',$this->_modules,array('id'=>$this->_data['id']));

	}
	
	public function save() {
		
		if (!$this->checkParams($this->modeltype)) {
			sendBack();
		}
		
		$flash=Flash::Instance();
		$errors=array();
		
		$data = $this->_data;
		$header = $data[$this->modeltype];
		
		if (isset($header['id']) && $header['id']!='') {
			$action='updated';
		} else {
			$action='added';
		}
		
		$trans_type = $this->_uses[$this->modeltype]->getEnum('transaction_type', $header['transaction_type']);
		
		$invoice = PInvoice::Factory($header, $errors);

		$result=false;
		if (count($errors)==0 && $invoice) {
			$result = $invoice->save();
				
			if(($result) && ($data['saveform']=='Save and Post')) {
		// reload the invoice to refresh the dependencies
				$invoice->load($invoice->id);
				if (!$invoice->post($errors)) {
					$result=false;
				}
			}
		}
			
		if($result!==FALSE) {
			$flash->addMessage($trans_type.' '.$action.' successfully');
			sendTo($this->name, 'view', $this->_modules, array('id'=>$invoice->id));
		}
		$errors[]='Error saving '.$trans_type;
		
		$flash->addErrors($errors);
		if (isset($header['id']) && $header['id']!='') {
			$this->_data['id']=$header['id'];
		}
		if (isset($header['plmaster_id']) && $header['plmaster_id']!='') {
			$this->_data['plmaster_id']=$header['plmaster_id'];
		}
		$this->refresh();
	}
	
	public function postInvoice() {
		$flash=Flash::Instance();
		$errors=array();
		$id = $this->_data['id'];
		$invoice  = DataObjectFactory::Factory('PInvoice');
		$invoice->load($id);
		$result = $invoice->post($errors);
		if($result!==false) {
			$flash->addMessage($invoice->getFormatted('transaction_type').' posted successfully');
			sendTo($this->name,'index',$this->_modules);
		}
		$flash->addErrors($errors);
		$flash->addError('Error saving '.$invoice->getFormatted('transaction_type'));
		sendBack();
	}

	public function selectinvoices() {
		$this->view->set('clickaction', 'view');
		$errors=array();
	
		$s_data=array();

// Set context from calling module
		if (isset($this->_data['slmaster_id'])) {
			$s_data['slmaster_id']=$this->_data['slmaster_id'];
		}
		$s_data['status']='N';
		
		$this->setSearch('pinvoicesSearch', 'useDefault', $s_data);

		$collection = new PInvoiceCollection($this->_templateobject);
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraint(new Constraint('line_count', '>', '0'));
		$sh->addConstraint(new Constraint('transaction_type', '!=', 'T'));
		
		parent::index($collection, $sh);
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'allinvoices'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ),
					'tag'=>'view all invoices'
				),
				'newinvoice'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ,'transaction_type'=>'I'
								 ),
					'tag'=>'new_sales_invoice'
				),
					'newcreditnote'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ,'transaction_type'=>'C'
								 ),
					'tag'=>'new_credit_note'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('page_title',$this->getPageName('','Post'));
		
	}

	public function batchprocess() {
		
		$flash = Flash::Instance();
		
		if (isset($this->_data['cancel'])) {
			$flash->addMessage('Posting of Purchase Invoices/Credit Notes Canceled');
			sendBack();
		}
		
		$errors=array();
		if (isset($this->_data['PInvoices']['selected'])) {
			$pinvoices=$this->_data['PInvoices'];
			foreach ($pinvoices['selected'] as $key=>$value) {
				if ($pinvoices['status'][$value]=='N') {
					$invoice=DataObjectFactory::Factory('PInvoice');
					$invoice->load($value);
					if ($invoice) {
						$invoice->post($errors);
					}
				}
			}
		} else {
			$errors[]='No invoices selected for posting';
		}
		
		if (count($errors)>0) {
			$flash->addErrors($errors);
		} else {
			$flash->addMessage('Posting of Purchase Invoices/Credit Notes Completed');
		}
		
		sendTo($this->name,'index',$this->_modules);
		
	}
	
	public function getNotes($_supplier_id='')
	{
// Used by Ajax to return Notes after selecting the Supplier
		if(!empty($this->_data['supplier_id'])) { $_supplier_id=$this->_data['supplier_id']; }
		
		$notes=new PartyNoteCollection();
		
		if ((!empty($_supplier_id)))
		{
			$supplier=DataObjectFactory::Factory('PLSupplier');
			
			$supplier->load($_supplier_id);
			
			$sh=new SearchHandler($notes, false);
			
			$sh->setFields(array('id','lastupdated','note'));
			
			$sh->setOrderby('lastupdated','DESC');
			
			$sh->addConstraint(new Constraint('note_type', '=', $this->module));
			$sh->addConstraint(new Constraint('party_id', '=', $supplier->companydetail->party_id));
			
			$notes->load($sh);
		}
		
		$this->view->set('no_ordering',true);
		$this->view->set('collection', $notes);
		
		if(isset($this->_data['ajax']))
		{
			$this->setTemplateName('datatable_inline');
		}
		else
		{
			return $this->view->fetch('datatable_inline');
		}
				
	}
	
	/* output functions */
	public function printInvoicelist($status='generate') {
		
		/*
		 * The sales version of this invoice never shows any lines
		 */
		
		// this function is very extensive, and thus we'll remove the max_execution_time
		set_time_limit(0);
		
		// construct title
		$title=$this->_data['type'].' Purchase Invoices';
		
		// build options array
		$options=array('type'		=>	array('pdf'=>'',
											  'xml'=>''
										),
					   'output'		=>	array('print'=>'',
					   						  'save'=>'',
					   						  'email'=>'',
					   						  'view'=>''
										),
					   'filename'	=>	$title,
					   'report'		=>	'InvoiceList'
				);

		  	
		if(strtolower($status)=="dialog") {
			return $options;
		}
		
		$invoices = new PInvoiceCollection($this->_templateobject);
		
		// load the model
		switch ($this->_data['type']) {
			case ('New'):
				$sh=new SearchHandler($invoices,false);
				$sh->setOrderby('due_date');
				$sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
				$sh->addConstraint(new Constraint('status', '=', 'N'));
				$title.=' as at '.fix_date(date(DATE_FORMAT));
				break;
			case ('Overdue'):
				$sh=new SearchHandler($invoices,false);
				$sh->setOrderby('due_date');
				$sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
				if (isset($this->_data['status'])) {
					$sh->addConstraint(new Constraint('status', '=', $this->_data['status']));
				} else {
					$sh->addConstraint(new Constraint('status', 'in', "('N', 'Q', 'O')"));
				}
				$sh->addConstraint(new Constraint('due_date', '<=', fix_date(date(DATE_FORMAT))));
				$title.=' as at '.fix_date(date(DATE_FORMAT));
				break;
			case ('Query'):
				$sh=new SearchHandler($invoices,false);
				$sh->setOrderby('due_date');
				$sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
				$sh->addConstraint(new Constraint('status', '=', 'Q'));
				break;
			case ('Day Book'):
				// fetch the search handler from cache
				$sh=$this->setSearchHandler($invoices,$this->_data['search_id'],true);
				$sh->setLimit(0);
				// get the date values to build the title
				$this->setSearch('pinvoicesSearch', 'useDefault', array());
				$date=$this->search->getValue('invoice_date');
				if (!empty($date) && is_array($date)) {
					$from_date=$date['from'];
					$to_date=$date['to'];
				} else {
					$from_date='';
					$to_date='';
				}
				if (!empty($from_date)) {
					if (!empty($to_date)) {
						if ($from_date==$to_date) {
							$title.=' for '.$from_date;
						} else {
							$title.=' from '.$from_date
								   .' to '.$to_date;
						}
					} else {
						$title.=' from '.$from_date;
					}
				} else {
					if (!empty($to_date)) {
						$title.=' to '.$to_date;
					}
				}
				if (empty($from_date) && empty($to_date)) {
					$title.=' for all invoices';
				}
				$sh->setOrderby('invoice_date');
				break;
		}
		
		$invoices->load($sh);

		$totals=array('base_net'=>0
					 ,'base_tax'=>0
					 ,'base_gross'=>0);
		foreach ($invoices as $invoice) {
			$totals['base_net']+=$invoice->base_net_value;
			$totals['base_tax']+=$invoice->base_tax_value;
			$totals['base_gross']+=$invoice->base_gross_value;
		}
		
		$params=DataObjectFactory::Factory('glparams');
		$base_currency=$params->base_currency_symbol();
		
		foreach($totals as $key => $value) {
			$totals[$key]=$base_currency.sprintf('%0.2f',$value);
		}
		
		$extra=array('totals'=>$totals,'title'=>$title);
		
		// generate the xml and add it to the options array
		$options['xmlSource']=$this->generateXML(array('model'=>$invoices,
													   'extra'=>$extra,
													   'load_relationships'=>FALSE
													  )
												);

		// execute the print output function, echo the returned json for jquery
		echo $this->constructOutput($this->_data['print'],$options);
		exit;
				
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'purchase_invoices':$base),$action);
	}

	public function getTaskList($_project_id='')	{
	
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['project_id'])) { $_project_id = $this->_data['project_id']; }
		}
		
		$tasks = $this->getOptions($this->_templateobject, 'task_id', '', '', '', array('project_id' => $_project_id));
			
		if(isset($this->_data['ajax']))
		{
			echo $tasks;
			exit;
		}
		
		return $tasks;
	
	}


}

// End of PinvoicesController
