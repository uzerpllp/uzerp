<?php

/**
 *	uzERP Sales Invoices Controller
 *
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class SinvoicesController extends printController
{

    protected $version = '$Revision: 1.85 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);
        $this->uses(DataObjectFactory::Factory('SInvoiceLine'), FALSE);
        $this->_templateobject = DataObjectFactory::Factory('SInvoice');
        $this->uses($this->_templateobject);

        // Define parameters for bulk output of posted Invoices
        // used by select_for_output function
        $this->output_types = array(
            'invoice' => array(
                'search_do' => 'sinvoicesSearch',
                'search_method' => 'invoices',
                'search_defaults' => array(),
                'collection' => 'SInvoiceCollection',
                'collection_fields' => array(
                    'id',
                    'invoice_number',
                    'invoice_date',
                    'customer',
                    'currency',
                    'gross_value',
                    'invoice_method as method',
                    'email_invoice as email'
                ),
                'display_fields' => array(
                    'invoice_number',
                    'invoice_date',
                    'customer',
                    'gross_value',
                    'currency',
                    'email'
                ),
                'identifier' => 'invoice_number',
                'title' => 'Select Invoices for ',
                'filename' => 'Invoice',
                'printaction' => 'batch_print_invoices'
            )
        );
    }

    public function index($collection = null, $sh = '', &$c_query = null)
    {
        $this->view->set('clickaction', 'view');
        $errors = array();

        $s_data = array();

        // set context from calling module
        if (isset($this->_data['slmaster_id'])) {
            $s_data['slmaster_id'] = $this->_data['slmaster_id'];
        }
        if (isset($this->_data['status'])) {
            $s_data['status'] = $this->_data['status'];
        }
        if (isset($this->_data['sales_order_number'])) {
            $s_data['sales_order_number'] = $this->_data['sales_order_number'];
        }
        if (isset($this->_data['from']) && isset($this->_data['to'])) {
            $s_data['invoice_date'] = array(
                'from' => un_fix_date($this->_data['from']),
                'to' => un_fix_date($this->_data['to'])
            );
        }

        $this->setSearch('sinvoicesSearch', 'useDefault', $s_data);

        $sl_analysis = $this->search->getValue('sl_analysis_id');

        parent::index(new SInvoiceCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);

        $actions = array();

        foreach ($this->_templateobject->getEnumOptions('transaction_type') as $key => $description) {
            $actions['new' . $description] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'transaction_type' => $key
                ),
                'tag' => 'new ' . $description
            );
        }

        $actions['printinvoices2'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'select_for_output',
                'type' => 'invoice'
            ),
            'tag' => 'send/print invoices'
        );

        $actions['printinvoices]'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'selectinvoices'
            ),
            'tag' => 'print/post manual invoices'
        );

        $sidebar->addList('Actions', $actions);

        $reports = array();

        $reports['newinvoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'printInvoicelist',
                'filename' => 'SInvoices_new' . fix_date(date(DATE_FORMAT)),
                'type' => 'New'
            ),
            'tag' => 'Unposted Invoices'
        );

        $reports['alloverdueinvoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'printInvoicelist',
                'filename' => 'SInvoices_overdue' . fix_date(date(DATE_FORMAT)),
                'type' => 'Overdue'
            ),
            'tag' => 'All Overdue invoices'
        );

        $reports['overdueinvoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'printInvoicelist',
                'filename' => 'SInvoices_overdue' . fix_date(date(DATE_FORMAT)),
                'type' => 'Overdue',
                'status' => 'O'
            ),
            'tag' => 'Overdue invoices Not in Query'
        );

        $reports['daybook'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'printInvoicelist',
                'filename' => 'SInvoices_daybook' . fix_date(date(DATE_FORMAT)),
                'type' => 'Day Book'
            ),
            'tag' => 'Day Book (uses current Search Settings)'
        );

        $sidebar->addList('Reports', $reports);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function clone_invoice()
    {
        $flash = Flash::Instance();

        $errors = array();

        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $invoice = $this->_uses[$this->modeltype];

        if (! $invoice->isLoaded()) {
            $flash->addError('Error loading invoice details');
            sendBack();
        }

        $data[$this->modeltype] = array();

        foreach ($invoice->getFields() as $fieldname => $field) {
            switch ($fieldname) {
                case $invoice->idField:
                case 'created':
                case 'createdby':
                case 'lastupdated':
                case 'alteredby':
                case 'invoice_number':
                case 'invoice_date':
                case 'despatch_date':
                case 'delivery_note':
                case 'date_printed':
                case 'print_count':
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

        if (! empty($this->_data['transaction_type'])) {
            $data[$this->modeltype]['transaction_type'] = $this->_data['transaction_type'];
        }

        $line_count = 0;

        foreach ($invoice->lines as $invoiceline) {
            $modelname = get_class($invoiceline);
            foreach ($invoiceline->getFields() as $fieldname => $field) {
                switch ($fieldname) {
                    case $invoiceline->idField:
                    case 'created':
                    case 'createdby':
                    case 'lastupdated':
                    case 'alteredby':
                    case 'delivery_note':
                        break;
                    case 'invoice_id':
                        $data[$modelname][$fieldname][$line_count] = '';
                        break;
                    case 'sales_order_id':
                    case 'order_line_id':
                        if ($this->_data['transaction_type'] == 'C') {
                            $data[$modelname][$fieldname][$line_count] = $invoiceline->$fieldname;
                        }
                        break;
                    case 'invoice_line_id':
                        if ($this->_data['transaction_type'] == 'C') {
                            $data[$modelname][$fieldname][$line_count] = $invoiceline->{$invoiceline->idField};
                        }
                        break;
                    case 'productline_id':
                        if (! is_null($invoiceline->productline_id)) {
                            $productline = DataObjectFactory::Factory('SOProductLine');
                            $productline->load($invoiceline->productline_id);
                            if (! $productline->isLoaded() || (! is_null($productline->end_date) && $productline->end_date < un_fix_date(date(DATE_FORMAT)))) {
                                $flash->addWarning('Selected Product is no longer valid on line ' . $invoiceline->line_number);
                                $invoiceline->description .= ' ** Selected Product is no longer valid';
                                $data[$modelname]['description'][$line_count] .= ' ** Selected Product is no longer valid';
                            }
                        }
                    default:
                        $data[$modelname][$fieldname][$line_count] = $invoiceline->$fieldname;
                }
            }
            $line_count ++;
        }

        $result = $invoice->save_model($data);
        if ($result !== FALSE) {
            sendTo($this->name, 'view', $this->_modules, array(
                $invoice->idField => $result['internal_id']
            ));
        }

        sendBack();
    }

    public function view()
    {
        $invoice = $this->_uses[$this->modeltype];

        if (isset($this->_data['id'])) {
            $id = $this->_data['id'];
            $invoice->load($id);
        } else {
            if (isset($this->_data['order_number'])) {
                $sorder = DataObjectFactory::Factory('SOrder');
                $sorder->loadBy('order_number', $this->_data['order_number']);
                $this->_data['order_id'] = $sorder->id;
            }

            if (isset($this->_data['order_id'])) {
                $invoice->loadBy('sales_order_id', $this->_data['order_id']);
                $id = $invoice->id;
            } elseif (isset($this->_data['invoice_number'])) {
                $invoice->loadBy('invoice_number', $this->_data['invoice_number']);
                $id = $invoice->id;
            }
        }

        $transaction_type = $invoice->getFormatted('transaction_type');

        $this->view->set('transaction_type', $transaction_type);

        $invoice->setTitle($transaction_type);

        $address = DataObjectFactory::Factory('address');

        if ($invoice->inv_address_id) {
            $address->load($invoice->inv_address_id);
        }

        $this->view->set('invoice_address', $address);

        $sidebar = new SidebarController($this->view);

        $actions = array();

        $actions['allInvoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            ),
            'tag' => 'view all invoices'
        );

        $actions['allcustomer'] = array(
            'link' => array(
                'module' => 'sales_ledger',
                'controller' => 'SLCustomers',
                'action' => 'index'
            ),
            'tag' => 'view all customers'
        );

        foreach ($invoice->getEnumOptions('transaction_type') as $key => $description) {
            $actions['new' . $description] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'transaction_type' => $key
                ),
                'tag' => 'new ' . $description
            );
        }

        $sidebar->addList('Actions', $actions);

        $actions = array();

        $actions['customerInvoices'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index',
                'slmaster_id' => $invoice->slmaster_id
            ),
            'tag' => 'view customer invoices'
        );

        $actions['customerOrders'] = array(
            'link' => array(
                'module' => 'sales_order',
                'controller' => 'sorders',
                'action' => 'index',
                'slmaster_id' => $invoice->slmaster_id
            ),
            'tag' => 'view customer orders'
        );

        foreach ($invoice->getEnumOptions('transaction_type') as $key => $description) {
            if ($key == 'C' || is_null($invoice->sales_order_id)) {
                $actions['new' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'new',
                        'slmaster_id' => $invoice->slmaster_id,
                        'transaction_type' => $key
                    ),
                    'tag' => 'new ' . $description
                );
            }
        }

        $sidebar->addList($invoice->customer, $actions);

        $actions = array();

        $actions['view'] = array(
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                'id' => $id
            ),
            'tag' => 'view current'
        );

        foreach ($invoice->getEnumOptions('transaction_type') as $key => $description) {
            if ($key == 'C' || is_null($invoice->sales_order_id)) {
                $actions['clone' . $description] = array(
                    'link' => array(
                        'modules' => $this->_modules,
                        'controller' => $this->name,
                        'action' => 'clone_invoice',
                        'id' => $id,
                        'transaction_type' => $key
                    ),
                    'tag' => 'Save as new ' . $description
                );
            }
        }

        if ($invoice->status == $invoice->newStatus()) {
            $actions['edit'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    'id' => $id
                ),
                'tag' => 'Edit'
            );
            $actions['add_lines'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'sinvoicelines',
                    'action' => 'new',
                    'invoice_id' => $id
                ),
                'tag' => 'Add_Lines'
            );
        }

        if ($invoice->onQuery()) {
            $actions['post'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'toggleQueryStatus',
                    'id' => $id
                ),
                'tag' => 'Take Invoice off Query'
            );
        }

        if ($invoice->hasBeenPostednotPaid()) {
            $actions['changeduedate'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'change_due_date',
                    'id' => $id
                ),
                'tag' => 'Change Due Date'
            );
            $actions['post'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'toggleQueryStatus',
                    'id' => $id
                ),
                'tag' => 'Put Invoice on Query'
            );
        }

        if (! $invoice->hasBeenPosted() && $invoice->lines->count() > 0 && $invoice->transaction_type != 'T') {
            $actions['post'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'postinvoice',
                    'id' => $id
                ),
                'tag' => 'post ' . $transaction_type
            );
        }

        if ($invoice->status != 'N') {
            $actions['viewGLtransaction'] = array(
                'link' => array(
                    'module' => 'general_ledger',
                    'controller' => 'gltransactions',
                    'action' => 'index',
                    'docref' => $invoice->invoice_number,
                    'source' => 'S',
                    'type' => 'I'
                ),
                'tag' => 'View GL transaction'
            );
        }

        if (! is_null($invoice->sales_order_id)) {
            $actions['viewdespatch'] = array(
                'link' => array(
                    'module' => 'despatch',
                    'controller' => 'sodespatchlines',
                    'action' => 'index',
                    'invoice_number' => $invoice->invoice_number
                ),
                'tag' => 'View Despatch Notes'
            );
        }

        if ($invoice->transaction_type != 'T') {
            $actions['printInvoice'] = array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printinvoice',
                    'filename' => 'SInv' . $invoice->invoice_number,
                    'id' => $invoice->id
                ),
                'tag' => 'Print ' . $transaction_type
            );
        }

        $sidebar->addList('This ' . $transaction_type, $actions);

        $this->sidebarRelatedItems($sidebar, $invoice);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function _new()
    {
        parent::_new();

        $sinvoice = $this->_uses[$this->modeltype];

        // get customer list
        if ($sinvoice->isLoaded() && $sinvoice->net_value != 0) {
            $customers = array(
                $sinvoice->slmaster_id => $sinvoice->customer
            );
        } else {
            $customers = $this->getOptions($this->_templateobject, 'slmaster_id', 'getOptions', 'getOptions', array(
                'use_collection' => true
            ));
            if (! $sinvoice->isLoaded()) {
                if (isset($this->_data['transaction_type'])) {
                    $sinvoice->transaction_type = $this->_data['transaction_type'];
                } else {
                    $sinvoice->transaction_type = 'I';
                }
            }
        }

        if (! is_null($sinvoice->transaction_type)) {
            $transaction_type_desc = $sinvoice->getFormatted('transaction_type');
            $this->view->set('transaction_type_desc', $transaction_type_desc);
        }

        $this->_templateobject->setTitle('Sales ' . $transaction_type_desc);

        // get the default/current selected customer
        if (isset($this->_data['slmaster_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $default_customer = $this->_data['slmaster_id'];
        } elseif (isset($this->_data[$this->modeltype]['slmaster_id'])) {
            // this is set if there has been error and we are redisplaying the screen
            $default_customer = $this->_data[$this->modeltype]['slmaster_id'];
        } else {
            if (! $sinvoice->isLoaded()) {
                $default_customer = $this->getDefaultValue($this->modeltype, 'slmaster_id', '');
            } else {
                $default_customer = $sinvoice->slmaster_id;
            }
        }

        if (empty($default_customer)) {
            $default_customer = key($customers);
        }

        if (! $sinvoice->isLoaded()) {
            $sinvoice->slmaster_id = $default_customer;
        }

        if ($sinvoice->isLoaded()) {
            $this->view->set('default_despatch_action', $sinvoice->despatch_action);
        } else {
            $this->view->set('default_despatch_action', $this->getDespatchAction($default_customer));
        }

        $this->view->set('selected_customer', $default_customer);
        $customer = $this->getCustomer($default_customer);
        $this->view->set('company_id', $customer->company_id);

        // get people based on default customer or first in customer
        $people = $this->getPeople($default_customer);
        $this->view->set('people', $people);

        if ($sinvoice->isLoaded()) {
            $default_person = $sinvoice->person_id;
        } elseif (isset($this->_data['person_id'])) {
            // this is set if there has been error nad we are redisplaying the screen
            $default_person = $this->_data['person_id'];
        } else {
            $default_person = $this->getDefaultValue($this->modeltype, 'person_id', '');
            if (empty($default_person)) {
                $default_person = key($people);
            }
        }
        $this->view->set('selected_people', $default_person);

        // get delivery address list for customer/person
        $delivery_address = $this->getPersonAddresses($default_person, 'shipping', $default_customer);
        $this->view->set('deliveryAddresses', $delivery_address);

        // get invoice address list for customer/person
        $invoice_address = $this->getPersonAddresses($default_person, 'billing', $default_customer);
        // Set default invoice address to delivery address if it exists
        // otherwise use customer default billing address
        if (isset($invoice_address[key($delivery_address)])) {
            $this->view->set('invoice_address', key($delivery_address));
            $this->view->set('invoiceAddresses', array(
                key($delivery_address) => current($delivery_address)
            ));
        } else {
            $this->view->set('invoice_address', $customer->billing_address_id);
            $this->view->set('invoiceAddresses', $invoice_address);
        }
        $this->view->set('default_inv_address', $customer->billing_address_id);

        // get Sales Invoice Notes for default customer or first in customer
        $this->getNotes($default_person, $default_customer);

        // Get despatch actions
        $despatch_actions = WHAction::getDespatchActions();
        $this->view->set('despatch_actions', $despatch_actions);

        // Get Projects and tasks
        $projects = $this->getProjects($default_customer);
        $this->view->set('projects', $projects);

        if (! $sinvoice->isLoaded() && ! empty($this->_data['project_id'])) {
            $sinvoice->project_id = $this->_data['project_id'];
        }

        $this->view->set('tasks', $this->getTaskList($sinvoice->project_id));
    }

    public function delete($modelName = null)
    {
        $flash = Flash::Instance();

        parent::delete($this->modeltype);

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function change_due_date()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $sinvoice = $this->_uses[$this->modeltype];

        $this->view->set('sinvoice', $sinvoice);
    }

    public function saveduedate()
    {
        // Very similar to toggleQueryStatus, could combine in single function?
        if (isset($this->_data[$this->modeltype])) {
            $flash = Flash::Instance();
            $errors = array();
            $db = DB::Instance();
            $db->StartTrans();

            $data = $this->_data[$this->modeltype];
            $sinvoice = DataObject::Factory($data, $errors, $this->modeltype);
            if ($sinvoice && count($errors) == 0 && $sinvoice->save()) {
                $due_date = fix_date($data['due_date']);
                $sltrans = DataObjectFactory::Factory('SLTransaction');
                $cc = new ConstraintChain();
                $cc->add(new Constraint('transaction_type', '=', $sinvoice->transaction_type));
                $cc->add(new Constraint('our_reference', '=', $data['invoice_number']));
                $sltrans->loadBy($cc);
                if ($sltrans->isLoaded()) {
                    if (! $sltrans->update($sltrans->id, 'due_date', $due_date)) {
                        $errors[] = 'Failed to update Ledger transaction';
                    }
                } else {
                    $errors[] = 'Cannot find Ledger transaction';
                }
            } else {
                $errors[] = 'Failed to update Invoice';
            }

            if (count($errors) == 0 && $db->CompleteTrans()) {
                $flash->addMessage('Due Date changed');
            } else {
                $flash->addErrors($errors);
                $flash->addError('Failed to change Due Date');
                $db->FailTrans();
            }
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $data['id']
            ));
        }
        sendTo($this->name, 'index', $this->_modules);
    }

    public function toggleQueryStatus()
    {
        // Very similar to saveduedate, could combine in single function?
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $sinvoice = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();
        $errors = array();

        $db = DB::Instance();
        $db->StartTrans();

        if ($sinvoice->status == 'Q') {
            $sinvoice->status = 'O';
            $status = 'taking Invoice off query';
        } else {
            $sinvoice->status = 'Q';
            $status = 'putting Invoice on query';
        }
        if (! $sinvoice->save()) {
            $errors[] = 'Failed to amend Invoice';
        }

        $sltrans = DataObjectFactory::Factory('SLTransaction');
        $cc = new ConstraintChain();
        $cc->add(new Constraint('transaction_type', '=', 'I'));
        $cc->add(new Constraint('our_reference', '=', $sinvoice->invoice_number));
        $sltrans->loadBy($cc);
        if ($sltrans) {
            if (! $sltrans->update($sltrans->id, 'status', $sinvoice->status)) {
                $errors[] = 'Failed to update Ledger transaction';
            }
        } else {
            $errors[] = 'Cannot find Ledger transaction';
        }
        if (count($errors) == 0 && $db->CompleteTrans()) {
            $flash->addMessage('Succeeded in ' . $status);
        } else {
            $flash->addErrors($errors);
            $flash->addError('Failed in ' . $status);
            $db->FailTrans();
        }
        sendTo($this->name, 'view', $this->_modules, array(
            'id' => $this->_data['id']
        ));
    }

    public function save($modelName = null, $dataIn = [], &$errors = []) : void
    {
        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $flash = Flash::Instance();
        $errors = array();

        $data = $this->_data;
        $header = $data[$this->modeltype];

        if (isset($header['id']) && $header['id'] != '') {
            $action = 'updated';
        } else {
            $action = 'added';
        }

        $trans_type = $this->_uses[$this->modeltype]->getEnum('transaction_type', $header['transaction_type']);

        $invoice = SInvoice::Factory($header, $errors);

        $result = false;
        if (count($errors) == 0 && $invoice) {
            $result = $invoice->save();

            if (($result) && $data['saveform'] == 'Save and Post') {
                // reload the invoice to refresh the dependencies
                $invoice->load($invoice->id);
                if (! $invoice->post($errors)) {
                    $result = false;
                }
            }
        }

        if ($result !== FALSE) {
            $flash->addMessage($trans_type . ' ' . $action . ' successfully');
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $invoice->id
            ));
        }
        $errors[] = 'Error saving ' . $trans_type;

        $flash->addErrors($errors);
        if (isset($header['id']) && $header['id'] != '') {
            $this->_data['id'] = $header['id'];
        }
        if (isset($header['slmaster_id']) && $header['slmaster_id'] != '') {
            $this->_data['slmaster_id'] = $header['slmaster_id'];
        }
        $this->refresh();
    }

    public function postInvoice()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $invoice = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();
        $errors = array();

        if ($invoice->post($errors)) {
            $flash->addMessage($invoice->getFormatted('transaction_type') . ' posted successfully');
            sendTo($this->name, 'index', $this->_modules);
        }
        $flash->addErrors($errors);
        $flash->addError('Error saving ' . $invoice->getFormatted('transaction_type'));
        sendBack();
    }

    public function getCustomer($_slmaster_id = '')
    {
        if (isset($this->_uses['SLCustomer'])) {
            $customer = $this->_uses['SLCustomer'];
        } else {
            $customer = DataObjectFactory::Factory('SLCustomer');
        }

        if (! $customer->isLoaded() && ! empty($_slmaster_id)) {
            $customer->load($_slmaster_id);
        }

        $this->uses($customer, false);

        return $customer;
    }

    public function getNotes($_person_id = '', $_slmaster_id = '')
    {
        // Used by Ajax to return Notes after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['person_id'])) {
                $_person_id = $this->_data['person_id'];
            }
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }

        $notes = new PartyNoteCollection();
        $party_id = '';
        if (! empty($_person_id)) {
            $person = DataObjectFactory::Factory('Person');
            $person->load($_person_id);
            $party_id = $person->party_id;
        }
        if (empty($party_id) && ! empty($_slmaster_id)) {
            $customer = $this->getCustomer($_slmaster_id);
            $party_id = $customer->companydetail->party_id;
        }
        if (! empty($party_id)) {
            $sh = new SearchHandler($notes, false);
            $sh->setFields(array(
                'id',
                'lastupdated',
                'note'
            ));
            $sh->setOrderby('lastupdated', 'DESC');
            $sh->addConstraint(new Constraint('note_type', '=', $this->module));
            $sh->addConstraint(new Constraint('party_id', '=', $party_id));
            $notes->load($sh);
        }
        $this->view->set('no_ordering', true);
        $this->view->set('collection', $notes);

        if (isset($this->_data['ajax'])) {
            $this->setTemplateName('datatable_inline');
        } else {
            return $this->view->fetch($this->getTemplateName('datatable_inline'));
        }
    }

    public function getPeople($_slmaster_id = '')
    {
        if ($_slmaster_id == '') {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);
        $cc = new ConstraintChain();
        if ($customer->isLoaded()) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('company_id', '=', $customer->company_id));
            $cc->add(new Constraint('end_date', 'IS', 'NULL'));
            $this->_templateobject->belongsTo[$this->_templateobject->belongsToField['person_id']]['cc'] = $cc;
        }

        $smarty_params = array(
            'nonone' => 'true',
            'depends' => 'slmaster_id'
        );
        unset($this->_data['depends']);

        return $this->getOptions($this->_templateobject, 'person_id', 'getPeople', 'getOptions', $smarty_params);
    }

    public function getPersonAddresses($_person_id = '', $_type = '', $_slmaster_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['person_id'])) {
                $_person_id = $this->_data['person_id'];
            }
            if (! empty($this->_data['type'])) {
                $_type = $this->_data['type'];
            }
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }
        $_data = Array(
            'type' => $_type,
            'slmaster_id' => $_slmaster_id
        );

        $addesses = $this->_templateobject->getPersonAddresses($_person_id, $_data);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $addesses);
            $this->setTemplateName('select_options');
        } else {
            return $addesses;
        }
    }

    /**
     * Set or return the default despatch action for the selected customer
     * 
     * Used by Ajax after the user selects a Customer, etc.
     *
     * @param string $_slmaster_id  sales ledger master record id
     * @return int || void
     */
    public function getDespatchAction($_slmaster_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['slmaster_id'])) {
                $_slmaster_id = $this->_data['slmaster_id'];
            }
        }

        $customer = $this->getCustomer($_slmaster_id);

        $despatch_action = '';
        if ($customer) {
            $despatch_action = $customer->despatch_action;
        }

        if (empty($despatch_action)) {
            $despatch_actions = WHAction::getDespatchActions();
            $despatch_action = current(array_keys($despatch_actions));
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $despatch_action);
            $this->setTemplateName('text_inner');
        } else {
            return $despatch_action;
        }
    }

    public function selectinvoices()
    {
        $this->view->set('clickaction', 'view');

        $this->setSearch('sinvoicesSearch', 'sinvoicePrintPost', ['status' => 'N']);

        $collection = new SInvoiceCollection($this->_templateobject);
        $sh = $this->setSearchHandler($collection);
        $sh->addConstraint(new Constraint('line_count', '>', '0'));
        $sh->addConstraint(new Constraint('transaction_type', '!=', 'T'));

        parent::index($collection, $sh);

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', array(
            'allinvoices' => array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                ),
                'tag' => 'view all invoices'
            ),
            'newinvoice' => array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'transaction_type' => 'I'
                ),
                'tag' => 'new_sales_invoice'
            ),
            'newcreditnote' => array(
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'transaction_type' => 'C'
                ),
                'tag' => 'new_credit_note'
            )
        ));
        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
        $this->view->set('printers', $this::selectPrinters());
        $this->view->set('default_printer', $this->getDefaultPrinter());
        $this->view->set('page_title', 'Print/Post Sales Invoices');

        // get data from persistent selection session
        $key = 'sales_invoicing-sinvoices-selectinvoices';

        $selected_rows = array();

        if (isset($_SESSION['persistent_selection'][$key])) {
            if (isset($this->_data['Search']['clear']) || isset($this->_data['Search']['search'])) {
                unset($_SESSION['persistent_selection'][$key]);
            } else {
                $selected_rows = $_SESSION['persistent_selection'][$key];
            }
        }

        $this->view->set('selected_rows', $selected_rows);
    }

    public function update_selected_sales_invoices()
    {
        $key = 'sales_invoicing-sinvoices-selectinvoices';

        if ($this->_data['selected'] == 'true') {
            $_SESSION['persistent_selection'][$key][$this->_data['id']] = $this->_data['status'];
        } else {
            unset($_SESSION['persistent_selection'][$key][$this->_data['id']]);
        }

        exit();
    }

    public function batchprocess()
    {
        $flash = Flash::Instance();
        $ajaxed = false;
        if (isset($this->_data['ajax'])) {
            unset($this->_data['ajax']);
            $ajaxed = true;
        }

        $sinvoices = $_SESSION['persistent_selection']['sales_invoicing-sinvoices-selectinvoices'];

        // Asked to process invoices matching the users search result
        if ($this->_data['process_matching'] == 'on') {
            $collection = new SInvoiceCollection($this->_templateobject);
            $t = new SearchHandler($collection, true, true, $this->_data['search_id']);
            $t->setLimit(0);
            $t->addConstraint(new Constraint('line_count', '>', '0'));
            $t->addConstraint(new Constraint('transaction_type', '!=', 'T'));
            $collection->load($t);
            $search_invoices = [];
            foreach ($collection as $row) {
                $search_invoices[$row->id] = $row->status;
            }
            // Ignore any indivdually selected invoices
            $sinvoices = $search_invoices;
        }

        $errors = array();

        if (!(isset($this->_data['print-invoices']) || isset($this->_data['post-invoices']))) {
            $errors[] = 'Please select one or more actions to perform.';
        }

        if (! empty($sinvoices && count($errors) == 0)) {

            // key = id
            // value = status

            foreach ($sinvoices as $key => $value) {

                // if print action selected
                if ($this->_data['print-invoices'] == 'on') {

                    // shape the data array
                    $this->_data['filename'] = '';
                    $this->_data['print'] = $this->_data;

                    // set the id
                    $this->_data['id'] = $key;

                    // fire the report
                    $this->printInvoice();

                    // unset the print array, so it's ready for the next loop
                    unset($this->_data['print']);
                }

                // if post action selected
                if ($this->_data['post-invoices'] == 'on') {

                    if ($value == 'N') {
                        $invoice = DataObjectFactory::Factory('SInvoice');
                        $invoice->load($key);
                        if ($invoice) {
                            $invoice->post($errors);
                        }
                    } else {
                        $errors['skipped'] = 'Posted invoices selected but ignored.';
                    }
                }
            }
        } else {
            $errors[] = 'No invoices selected for print/post';
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
        } else {
            unset($_SESSION['persistent_selection']['sales_invoicing-sinvoices-selectinvoices']);
            $flash->addMessage('Print/Post Sales Invoices/Credit Notes Completed');
        }

        if ($ajaxed) {
            sendback();
        }
        sendTo($this->name, 'index', $this->_modules);
        
    }

    public function printaction()
    {
        if (strtolower($this->_data['printaction']) == 'printinvoice') {
            if (! $this->loadData()) {
                $this->dataError();
                sendBack();
            }
            $invoice = $this->_uses[$this->modeltype];
            $customer = $this->getCustomer($invoice->slmaster_id);
            $invoice_methods = $customer->getEnumOptions('invoice_method');
            if (isset($invoice_methods[$customer->invoice_method])) {
                $this->defaultprintaction = $invoice_methods[$customer->invoice_method];
            }
            $this->view->set('email', $customer->email_invoice());
        }
        parent::printAction();
    }

    public function sorders_summary()
    {
        $orders = new SInvoiceLineCollection();
        $customersales = $orders->getTopSales(10, $this->_data['type']);
        $this->view->set('content', $customersales);
    }

    public function view_Transactions()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $works_order = $this->_uses[$this->modeltype];

        $transaction = DataObjectFactory::Factory('STTransaction');
        $transaction->setDefaultDisplayFields(array(
            'stitem' => 'stock_item',
            'created',
            'flocation' => 'from_location',
            'fbin' => 'from_bin',
            'whlocation' => 'to_location',
            'whbin' => 'to_bin',
            'qty',
            'error_qty',
            'balance',
            'status',
            'remarks'
        ));

        $related_collection = new STTransactionCollection($transaction);

        $sh = $this->setSearchHandler($related_collection);

        $sh->addConstraint(new Constraint('process_id', '=', $works_order->id));
        $sh->addConstraint(new Constraint('qty', '>=', 0));
        $sh->addConstraint(new Constraint('error_qty', '>=', 0));

        $cc = new ConstraintChain();
        $cc->add(new Constraint('process_name', '=', 'SI'));
        $cc->add(new Constraint('process_name', '=', 'SC'), 'OR');
        $sh->addConstraint($cc);

        parent::index($related_collection, $sh);

        $this->_templateName = $this->getTemplateName('view_related');
        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'stitems');
        $this->view->set('linkvaluefield', 'stitem_id');
        $this->view->set('related_collection', $related_collection);
        $this->view->set('collection', $related_collection);
        $this->view->set('no_ordering', true);
    }

    /* output functions */
    public function batch_print_invoices($_invoice_id, $_print_params)
    {
        /* batch_print_invoices() is a wrapper for printInvoice(), to be used via select_for_output */
        $this->_data['id'] = $_invoice_id;
        $this->_data['print'] = $_print_params;

        $response = json_decode($this->printInvoice(), true);

        // bit paranoid about the data array being contaminated
        unset($this->_data['id'], $this->_data['print']);

        return $response['status'];
    }

    public function printInvoice($status = 'generate')
    {

        /*
         * dialog will be delivered with all appropriate screens
         * these screens will be switched between depending on the
         * server / user choices...
         *
         */

        // should this extend a common function, and we just pass through the data?

        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            )
        );
        
        // Don't go and get the model from the construct, we need to fresh model to prevent the first object from repeating n times
        $invoice = DataObjectFactory::Factory('SInvoice');
        $invoice->load($this->_data['id']);

        // ATTN: would these need an EDI option too?
        $invoice_number = $invoice->invoice_number;

        $customer = $this->getCustomer($invoice->slmaster_id);
        if (strtolower($this->_data['printaction']) == 'printinvoice') {
            $invoice_methods = $customer->getEnumOptions('invoice_method');
            if (isset($invoice_methods[$customer->invoice_method])) {
                $options['default_print_action'] = strtolower($invoice_methods[$customer->invoice_method]);
            }
            $options['email_subject'] = '"Sales Invoice, No: ' . $invoice_number . '"';
            $options['email'] = $customer->email_invoice();
        }

        $sc = new Systemcompany();
        $sc->load(COMPANY_ID);
        $options['replyto'] = $sc->getInvoiceReplyToEmailAddress();

        // If the customer's statement email is not set,
        // then use the users employee work email
        // or the user email as a last resort.
        if (empty($options['replyto'])) {
            $user = DataObjectFactory::Factory('user');
            $user->load($_SESSION['username']);
            $person = DataObjectFactory::Factory('Person');
            $person->load($user->person_id);
            $options['replyto'] = $person->email->contactmethod->contact;
        }

        if (empty($options['replyto']) || !is_string($options['replyto'])) {
            $options['replyto'] = $user->email;
        }
        
        // Set the XSL:FO template to be used
        $invoice_layout = 'SalesInvoice';

        // Get ledger_setup module preferences
        $system_prefs = SystemPreferences::instance();
        $ledger_prefs = $system_prefs->getModulePreferences('ledger_setup');

        if (!is_null($customer->report_def_id) && isset($ledger_prefs['sales-invoice-report-type']) && $ledger_prefs['sales-invoice-report-type'] !== '') {
            $def = new ReportDefinition();
            $def->load($customer->report_def_id);
            $invoice_layout = $def->name;
        }

        if (!isset($options['email_subject'])) {
            // This sets the email subject for batch output.
            // No dialog was shown to the user so the subject
            // is not available in the controller's data.
            $options['email_subject'] = 'Sales Invoice, No: ' . $invoice_number . (is_null($invoice->ext_reference) ? '' : ' Your Ref: ' . $invoice->ext_reference);
        }
        
        $options['report'] = $invoice_layout;
        $options['filename'] = 'SInv' . $invoice_number;

        // if we're dealing with the dialog, just return the options...
        if (strtolower($status) == 'dialog') {
            return $options;
        }

        // ...otherwise continue with the function

        // we need to do something different for EDI invoicing
        if ($this->_data['print']['printaction'] == 'EDI' && ! is_null($invoice->customerdetail->edi_invoice_definition_id)) {
            // ATTENTION: OUTPUT: this needs testing aganist EDI code
            $errors = array();
            $datadef = DataObjectFactory::Factory('DataDefinition');
            $datadef->load($invoice->customerdetail->edi_invoice_definition_id);
            $filename = $datadef->file_prefix . $invoice->invoice_number . '.' . strtolower($datadef->file_extension);
            $edi = $datadef->setEdiInterface();
            $edi->processFile(array(
                'filename' => $filename,
                'data' => $invoice->id
            ), $errors);

            if (count($errors) > 0) {
                echo json_encode(array(
                    'status' => false,
                    'message' => 'EDI failed'
                ));
            } else {
                echo json_encode(array(
                    'status' => true,
                    'message' => 'EDI success'
                ));
            }

            exit();
        } else {
            $extra = array();

            $tax_status = DataObjectFactory::Factory('TaxStatus');
            $tax_status->load($invoice->tax_status_id);

            // collect all possible delivery notes, display them comma delimited
            $delivery_note_arr = array();
            foreach ($invoice->lines as $invoicelines) {
                $delivery_note_arr[$invoicelines->_data['delivery_note']] = $invoicelines->_data['delivery_note'];
            }
            $delivery_note = implode(",", $delivery_note_arr);
            $extra['delivery_note'] = $delivery_note;

            // get the company address
            $company_address = array(
                'name' => $this->getCompanyName()
            );
            $company_address += $this->formatAddress($this->getCompanyAddress());
            $extra['company_address'] = $company_address;

            // get the company details
            $extra['company_details'] = $this->getCompanyDetails();

            // get the invoice address
            $invoice_address = array();
            $invoice_address['name'] = $invoice->customer;
            if (! is_null($invoice->person_id)) {
                $invoice_address['person'] = $invoice->person;
            }
            $invoice_address += (array) $this->formatAddress($invoice->getInvoiceAddress());
            $extra['invoice_address'] = $invoice_address;
            $extra['customer_number'] = $invoice->customerdetail->accountnumber();
            $extra['tax_description'] = $invoice->customerdetail->companydetail->tax_description;
            $extra['vatnumber'] = $invoice->customerdetail->companydetail->vatnumber;

            $extra['additional_text1'] = $invoice->customerdetail->companydetail->text1;
            $extra['additional_text2'] = $invoice->customerdetail->companydetail->text2;

            // get Sales Invoice Notes for default customer or first in customer
            $note = DataObjectFactory::Factory('PartyNote');
            $party_id = $invoice->customerdetail->companydetail->party_id;

            $cc = new ConstraintChain();
            $note->orderby = 'lastupdated';
            $note->orderdir = 'DESC';
            $cc->add(new Constraint('note_type', '=', $this->module));
            $cc->add(new Constraint('party_id', '=', $party_id));

            $latest_note = $note->loadBy($cc);
            $extra['notes'] = $latest_note->note;

            // get the delivery address
            $delivery_address = $invoice->customer . ", " . $invoice->getDeliveryAddress()->fulladdress;
            $extra['delivery_address'] = $delivery_address;
            
            // get the VAT number associated with the delivery address
            $ship = DataObjectFactory::Factory('PartyAddress');
            $ship->load($invoice->del_partyaddress_id);
            $extra['delivery_address_name'] = $ship->name;
            $extra['delivery_address_vatnumber'] = $ship->vatnumber;
            $extra['delivery_address_notes'] = $ship->notes;

            // add sales order text1,2,3 fields
            $extra['sales_order_header_text1'] = $invoice->order->text1;
            $extra['sales_order_header_text2'] = $invoice->order->text2;
            $extra['sales_order_header_text3'] = $invoice->order->text3;

            // get the settlement terms
            if ($invoice->transaction_type == 'I') {

                $extra['settlement_terms'] = $invoice->getSettlementTerms();

                $bank_account = $invoice->customerdetail->bank_account_detail;

                if (! is_null($bank_account->bank_account_number)) {
                    $extra['bank_account']['bank_name'] = $bank_account->bank_name;
                    $extra['bank_account']['bank_sort_code'] = $bank_account->bank_sort_code;
                    $extra['bank_account']['bank_account_number'] = $bank_account->bank_account_number;
                    $extra['bank_account']['bank_address'] = $bank_account->bank_address;
                    $extra['bank_account']['bank_iban_number'] = $bank_account->bank_iban_number;
                    $extra['bank_account']['bank_bic_code'] = $bank_account->bank_bic_code;
                }
            }

            // get invoice totals
            $invoice_totals = array();
            $invoice_totals[]['line'][] = array(
                'field1' => 'NET VALUE',
                'field2' => $invoice->net_value . ' ' . $invoice->currency
            );
            $invoice_totals[]['line'][] = array(
                'field1' => 'VAT',
                'field2' => $invoice->tax_value . ' ' . $invoice->currency
            );
            $invoice_totals[]['line'][] = array(
                'field1' => strtoupper($invoice->getFormatted('transaction_type')) . ' TOTAL',
                'field2' => $invoice->gross_value . ' ' . $invoice->currency
            );
            $extra['invoice_totals'] = $invoice_totals;

            // get invoice vat analysis
            if ($tax_status->eu_tax == 't' && $tax_status->apply_tax == 'f') {
                $extra['vat_analysis_exempt'][]['line'] = 'Zero rated intra-EU supply';
                $extra['vat_analysis_exempt'][]['line'] = 'Not subject to UK VAT under Article 56 Directive 2006/112/EC';
            } else {
                $vat_analysis = $invoice->vatAnalysis();
                foreach ($vat_analysis as $key => $value) {
                    $extra['vat_analysis'][]['line'][] = $value;
                }
            }

            // generate the XML, include the extras array too
            $xml = $this->generateXML(array(
                'model' => $invoice,
                'relationship_whitelist' => array(
                    'lines'
                ),
                'extra' => $extra
            ));

            // apple the xml to the options array
            $options['xmlSource'] = $xml;

            // construct the document, capture the response
            $json_response = $this->generate_output($this->_data['print'], $options);

            // decode response, if it was successful update the print count
            $response = json_decode($json_response);
            if ($response->status === true) {
                $invoice->update($this->_data['id'], array(
                    'date_printed',
                    'print_count'
                ), array(
                    fix_date(date(DATE_FORMAT)),
                    $invoice->print_count + 1
                ));
            }
            // now we've done our checks, output the original JSON for jQuery to use
            // echo the response if we're using ajax, return the response otherwise
            if (isset($this->_data['ajax'])) {
                echo $json_response;
            } else {
                return $json_response;
            }

            exit();
        }

        exit();
    }

    public function printInvoicelist($status = 'generate')
    {

        /*
         * The sales version of this invoice never shows any lines
         */

        // this function is very extensive, and thus we'll remove the max_execution_time
        set_time_limit(0);

        // construct title
        $title = $this->_data['type'] . ' Sales Invoices';

        // build options array
        $options = array(
            'type' => array(
                'pdf' => '',
                'xml' => ''
            ),
            'output' => array(
                'print' => '',
                'save' => '',
                'email' => '',
                'view' => ''
            ),
            'filename' => $title,
            'report' => 'InvoiceList'
        );

        if (strtolower($status) == "dialog") {
            return $options;
        }

        $invoices = new SInvoiceCollection($this->_templateobject);

        // load the model
        switch ($this->_data['type']) {
            case ('New'):
                $sh = new SearchHandler($invoices, false);
                $sh->setOrderby('due_date');
                $sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
                $sh->addConstraint(new Constraint('status', '=', 'N'));
                $title .= ' as at ' . un_fix_date(fix_date(date(DATE_FORMAT)));
                break;
            case ('Overdue'):
                $sh = new SearchHandler($invoices, false);
                $sh->setOrderby('due_date');
                $sh->addConstraint(new Constraint('transaction_type', '=', 'I'));
                if (isset($this->_data['status'])) {
                    $sh->addConstraint(new Constraint('status', '=', $this->_data['status']));
                } else {
                    $sh->addConstraint(new Constraint('status', 'in', "('N', 'Q', 'O')"));
                }
                $sh->addConstraint(new Constraint('due_date', '<=', fix_date(date(DATE_FORMAT))));
                $title .= ' as at ' . un_fix_date(fix_date(date(DATE_FORMAT)));
                break;
            case ('Day Book'):
                // fetch the search handler from cache
                $sh = $this->setSearchHandler($invoices, $this->_data['search_id'], true);
                $sh->setLimit(0);
                // get the date values to build the title
                $this->setSearch('sinvoicesSearch', 'useDefault', array());
                $date = $this->search->getValue('invoice_date');
                if (! empty($date) && is_array($date)) {
                    $from_date = $date['from'];
                    $to_date = $date['to'];
                } else {
                    $from_date = '';
                    $to_date = '';
                }
                if (! empty($from_date)) {
                    if (! empty($to_date)) {
                        if ($from_date == $to_date) {
                            $title .= ' for ' . $from_date;
                        } else {
                            $title .= ' from ' . $from_date . ' to ' . $to_date;
                        }
                    } else {
                        $title .= ' from ' . $from_date;
                    }
                } else {
                    if (! empty($to_date)) {
                        $title .= ' to ' . $to_date;
                    }
                }
                if (empty($from_date) && empty($to_date)) {
                    $title .= ' for all invoices';
                }
                $sh->setOrderby('invoice_date');
                break;
        }

        $invoices->load($sh);

        $totals = array(
            'base_net' => 0,
            'base_tax' => 0,
            'base_gross' => 0
        );
        foreach ($invoices as $invoice) {
            $totals['base_net'] += $invoice->base_net_value;
            $totals['base_tax'] += $invoice->base_tax_value;
            $totals['base_gross'] += $invoice->base_gross_value;
        }

        $params = DataObjectFactory::Factory('glparams');
        $base_currency = $params->base_currency_symbol();

        foreach ($totals as $key => $value) {
            $totals[$key] = $base_currency . sprintf('%0.2f', $value);
        }

        $extra = array(
            'totals' => $totals,
            'title' => $title
        );

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $invoices,
            'extra' => $extra,
            'load_relationships' => FALSE
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    /* consolodation functions */
    public function getCustomerData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        $this->_data['ajax_call'] = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_slmaster_id = $this->_data['slmaster_id'];
        $_product_search = $this->_data['product_search'];
        $customer = $this->getCustomer($_slmaster_id);

        $person_id = $this->getPeople($_slmaster_id);
        $output['person_id'] = array(
            'data' => $person_id,
            'is_array' => is_array($person_id)
        );

        $project_id = $this->getProjects($_slmaster_id);
        $output['project_id'] = array(
            'data' => $project_id,
            'is_array' => is_array($project_id)
        );

        $output['company_id'] = array(
            'data' => $customer->company_id,
            'is_array' => false
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    public function getPersonData()
    {
        // store the ajax status in a different var, then unset the current one
        // we do this because we don't want the functions we all to get confused
        $ajax = isset($this->_data['ajax']);
        unset($this->_data['ajax']);

        // set vars
        $_person_id = $this->_data['person_id'];
        $_slmaster_id = $this->_data['slmaster_id'];
        $_del_type = $this->_data['del_type'];
        $_inv_type = $this->_data['inv_type'];

        $del_address_id = $this->getPersonAddresses($_person_id, $_del_type, $_slmaster_id);
        $output['del_address_id'] = array(
            'data' => $del_address_id,
            'is_array' => is_array($del_address_id)
        );

        $inv_address_id = $this->getPersonAddresses($_person_id, $_inv_type, $_slmaster_id);
        $output['inv_address_id'] = array(
            'data' => $inv_address_id,
            'is_array' => is_array($inv_address_id)
        );

        $notes = $this->getNotes($_person_id, $_slmaster_id);
        $output['notes'] = array(
            'data' => $notes,
            'is_array' => is_array($notes)
        );

        // could we return the data as an array here? save having to re use it in the new / edit?
        // do a condition on $ajax, and return the array if false
        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    public function getProjects($_slmaster_id = '')
    {
        // Used by Ajax to return projects for a customer after selecting the Customer
        if ($_slmaster_id == '') {
            $_slmaster_id = $this->_data['slmaster_id'];
        }

        $customer = $this->getCustomer($_slmaster_id);
        $cc = new ConstraintChain();
        if ($customer->isLoaded()) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('archived', '=', FALSE));
            $cc->add(new Constraint('company_id', '=', $customer->company_id));
            $this->_templateobject->belongsTo[$this->_templateobject->belongsToField['project_id']]['cc'] = $cc;
        }

        $smarty_params = array(
            'nonone' => 'true',
            'depends' => 'slmaster_id'
        );
        unset($this->_data['depends']);

        return $this->getOptions($this->_templateobject, 'project_id', 'getProjects', 'getOptions', $smarty_params);
    }

    public function getTaskList($_project_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['project_id'])) {
                $_project_id = $this->_data['project_id'];
            }
        }

        $tasks = $this->getOptions($this->_templateobject, 'task_id', '', '', '', array(
            'project_id' => $_project_id
        ));

        if (isset($this->_data['ajax'])) {
            echo $tasks;
            exit();
        }

        return $tasks;
    }

    protected function getPageName($base = null, $action = null)
    {
        return parent::getPageName((empty($base) ? 'sales_invoices' : $base), $action);
    }
}

// End of SinvoicesController
