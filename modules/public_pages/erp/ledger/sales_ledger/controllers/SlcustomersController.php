<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class SlcustomersController extends LedgerController
{

    protected $version = '$Revision: 1.93 $';

    protected $_templateobject;

    protected $search_details;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->uses(DataObjectFactory::Factory('CBTransaction'), FALSE);
        $this->uses(DataObjectFactory::Factory('SLTransaction'), FALSE);

        $this->_templateobject = DataObjectFactory::Factory('SLCustomer');
        $this->uses($this->_templateobject);

        // Define parameters for bulk output Statements
        // used by select_for_output function
        $this->output_types = array(
            'statement' => array(
                'search_do' => 'SLCustomerSearch',
                'search_method' => 'statements',
                'search_defaults' => array(
                    'statement' => 'TRUE'
                ),
                'collection' => 'SLCustomerCollection',
                'collection_fields' => array(
                    'id',
                    'name',
                    'currency',
                    'outstanding_balance',
                    'email_statement as email',
                    'last_statement_date'
                ),
                'display_fields' => array(
                    'name',
                    'outstanding_balance',
                    'currency',
                    'last_statement_date',
                    'email'
                ),
                'title' => 'Select Statements for ',
                'filename' => 'Statement',
                'printaction' => 'print_customer_statements'
            )
        );
    }

    public function index()
    {

        // Search
        $errors = array();
        $s_data = array();

        // Set context from calling module
        $s_data['name'] = '';
        $s_data['currency_id'] = '';
        $s_data['remittance_advice'] = '';
        $s_data['invoice_method'] = '';
        $s_data['payment_type_id'] = '';

        $this->setSearch('SLCustomerSearch', 'useDefault', $s_data);
        // End of search

        $this->view->set('clickaction', 'view');
        parent::index(new SLCustomerCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);
        $sidebar->addList('Actions', array(
            'all' => array(
                'tag' => 'View all customers',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'new' => array(
                'tag' => 'new_Customer',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new'
                )
            ),
            'receive_payment' => array(
                'tag' => 'receive_payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'receive_payment'
                )
            ),
            'make_refund' => array(
                'tag' => 'make_refund',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_refund'
                )
            ),
            'enter_journal' => array(
                'tag' => 'Enter SL Journal',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'enter_journal'
                )
            ),
            'periodic_payments' => array(
                'tag' => 'periodic_payments',
                'link' => array(
                    'module' => 'cashbook',
                    'controller' => 'Periodicpayments',
                    'action' => 'index',
                    'source' => 'SR'
                )
            )
        ));

        $sidebar->addList('reports', array(
            'printstatement' => array(
                'tag' => 'Output Statements',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'select_for_output',
                    'type' => 'statement'
                )
            )
        ));

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function view()
    {
        if (! isset($this->_data) || ! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];
        $address = $customer->getBillingAddress();
        $this->view->set('billing_address', $address);

        $idField = $customer->idField;
        $idValue = $customer->$idField;

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('Actions', array(
            $customer->name => array(
                'tag' => 'View All Customers',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index'
                )
            ),
            'enter_journal' => array(
                'tag' => 'Enter SL Journal',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'enter_journal'
                )
            ),
            'receive_payment' => array(
                'tag' => 'Receive Payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'receive_payment'
                )
            ),
            'make_refund' => array(
                'tag' => 'Make Refund',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_refund'
                )
            ),
            'periodic_payments' => array(
                'tag' => 'periodic_payments',
                'link' => array(
                    'module' => 'cashbook',
                    'controller' => 'Periodicpayments',
                    'action' => 'index',
                    'source' => 'SR'
                )
            )
        ));

        $sidebarlist = array();

        $sidebarlist[$customer->name] = array(
            'tag' => $customer->name,
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view',
                $idField => $idValue
            )
        );
        $sidebarlist['edit'] = array(
            'tag' => 'Edit',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'edit',
                $idField => $idValue
            )
        );
        $sidebarlist['newinvoice'] = array(
            'tag' => 'New Invoice',
            'link' => array(
                'module' => 'sales_invoicing',
                'controller' => 'sinvoices',
                'action' => 'new',
                'slmaster_id' => $idValue,
                'transaction_type' => 'I',
                'payment_term_id' => $customer->payment_term_id,
                'currency_id' => $customer->currency_id
            )
        );
        $sidebarlist['newcreditnote'] = array(
            'tag' => 'New Credit Note',
            'link' => array(
                'module' => 'sales_invoicing',
                'controller' => 'sinvoices',
                'action' => 'new',
                'slmaster_id' => $idValue,
                'transaction_type' => 'C',
                'payment_term_id' => $customer->payment_term_id,
                'currency_id' => $customer->currency_id
            )
        );
        $sidebarlist['newtrans'] = array(
            'tag' => 'Enter SL Journal',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'enter_journal',
                'slmaster_id' => $idValue
            )
        );
        $sidebarlist['receive_payment'] = array(
            'tag' => 'receive_payment',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'receive_payment',
                'slmaster_id' => $idValue
            )
        );
        $sidebarlist['make_refund'] = array(
            'tag' => 'make_refund',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'make_refund',
                'slmaster_id' => $idValue
            )
        );
        $sidebarlist['periodic_payments'] = array(
            'tag' => 'periodic_payments',
            'link' => array(
                'module' => 'cashbook',
                'controller' => 'Periodicpayments',
                'action' => 'index',
                'source' => 'SR',
                'company_id' => $customer->company_id
            )
        );
        $sidebarlist['account_status'] = array(
            'tag' => ($customer->accountStopped() ? 'Open' : 'Stop') . ' account',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'updatestatus',
                $idField => $idValue,
                'account_status' => ($customer->accountStopped() ? $customer->openStatus() : $customer->stopStatus())
            )
        );

        if ($customer->canDelete()) {
            $sidebarlist['delete'] = array(
                'tag' => 'delete',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'delete',
                    $idField => $idValue
                )
            );
        }

        if (! is_null($customer->date_inactive)) {
            $sidebarlist['inactive'] = array(
                'tag' => 'Make Active',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_active',
                    $idField => $idValue
                )
            );
        } elseif (! $customer->hasCurrentActivity()) {
            $sidebarlist['inactive'] = array(
                'tag' => 'Make Inactive',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_inactive',
                    $idField => $idValue
                )
            );
        }

        $sidebar->addList('currently_viewing', $sidebarlist);

        $sidebarlist = array();

        if ($customer->outstanding_balance != 0) {
            $sidebarlist['printstatement'] = array(
                'tag' => 'Print Statement',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'printDialog',
                    'printaction' => 'printStatement',
                    'id' => $customer->id
                )
            );
        }

        $sidebar->addList('reports', $sidebarlist);

        $sidebarlist = array();

        $sidebarlist['allocate'] = array(
            'tag' => 'allocate',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'allocate',
                'id' => $customer->id
            )
        );
        $sidebarlist['outstanding'] = array(
            'tag' => 'Outstanding',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'outstanding_transactions',
                'id' => $customer->id
            )
        );
        $sidebarlist['inquery'] = array(
            'tag' => 'In Query',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'inquery_transactions',
                'id' => $customer->id
            )
        );
        $sidebarlist['all'] = array(
            'tag' => 'All',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'all_transactions',
                'id' => $customer->id
            )
        );
        $sidebarlist['viewunposted'] = array(
            'tag' => 'View unposted invoices',
            'link' => array(
                'module' => 'sales_invoicing',
                'controller' => 'sinvoices',
                'action' => 'index',
                'slmaster_id' => $customer->id,
                'status' => 'N'
            )
        );
        $sidebarlist['viewinvoices'] = array(
            'tag' => 'View all invoices',
            'link' => array(
                'module' => 'sales_invoicing',
                'controller' => 'sinvoices',
                'action' => 'index',
                'slmaster_id' => $customer->id
            )
        );
        $sidebarlist['viewcontact_details'] = array(
            'tag' => 'View Contact Details',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'viewcontact_methods',
                'id' => $customer->id
            )
        );
        $sidebarlist['viewdiscounts'] = array(
            'tag' => 'View Customer Discounts',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'Sldiscounts',
                'action' => 'viewSLCustomers',
                'slmaster_id' => $customer->id
            ),
            'new' => array(
                'modules' => $this->_modules,
                'controller' => 'Sldiscounts',
                'action' => 'new',
                'slmaster_id' => $customer->id
            )
        );

        $sidebarlist['vieworders'] = array(
            'tag' => 'View Customer Orders',
            'link' => array(
                'module' => 'sales_order',
                'controller' => 'sorders',
                'action' => 'index',
                'slmaster_id' => $customer->id
            ),
            'new' => array(
                'module' => 'sales_order',
                'controller' => 'sorders',
                'action' => 'new',
                'slmaster_id' => $customer->id
            )
        );

        $sidebarlist['viewprices'] = array(
            'tag' => 'View Customer Prices',
            'link' => array(
                'module' => 'sales_order',
                'controller' => 'soproductlines',
                'action' => 'index',
                'slmaster_id' => $customer->id
            )
        );

        $sidebarlist['viewnotes'] = array(
            'tag' => 'View Notes',
            'link' => array(
                'module' => 'contacts',
                'controller' => 'partynotes',
                'action' => 'index',
                'party_id' => $customer->getPartyID()
            ),
            'new' => array(
                'module' => 'contacts',
                'controller' => 'partynotes',
                'action' => 'new',
                'party_id' => $customer->getPartyID()
            )
        );

        $sidebar->addList('related_items', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function make_active()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        $customer->date_inactive = null;

        $db = DB::Instance();
        $db->StartTrans();

        if (! $customer->save()) {
            $flash->addError('Error making customer inactive: ' . $db->ErrorMsg());
            $db->FailTrans();
        }

        $db->CompleteTrans();

        sendBack();
    }

    public function make_inactive()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        // Check to make sure no-one has updated the customer
        if ($customer->hasCurrentActivity()) {
            $flash->addError('Error making customer inactive - customer is still active');
        } else {
            $customer->date_inactive = fix_date(date(DATE_FORMAT));

            $db = DB::Instance();
            $db->StartTrans();

            if (! $customer->save()) {
                $flash->addError('Error making customer inactive: ' . $db->ErrorMsg());
                $db->FailTrans();
            } else {
                // Now close off any open SO Product Lines for the Customer
                $soproductline = DataObjectFactory::Factory('SOProductline');
                $soproductlines = new SOProductlineCollection($soproductline);

                $sh = new SearchHandler($soproductlines, FALSE);

                $sh->addConstraintChain($soproductline->currentConstraint());
                $sh->addConstraint(new Constraint('slmaster_id', '=', $customer->id));

                if ($soproductlines->update('end_date', $customer->date_inactive, $sh) !== FALSE) {
                    $flash->addMessage('Customer marked as inactive');
                } else {
                    $flash->addError('Error closing off customer product lines: ' . $db->ErrorMsg());
                    $db->FailTrans();
                }
            }

            $db->CompleteTrans();
        }

        sendBack();
    }

    public function getCustomerList()
    {
        return $this->getOptions($this->_uses['SLTransaction'], 'slmaster_id', 'getCustomerList', 'getOptions', array(
            'use_collection' => true
        ));
    }

    public function enter_journal()
    {
        $customer = $this->_uses[$this->modeltype];

        $customer_list = $this->getCustomerList();
        $this->view->set('companies', $customer_list);

        if (isset($this->_data['slmaster_id'])) {
            $customer_id = $this->_data['slmaster_id'];
            $this->_data['currency_id'] = $customer->currency_id;
        } else {
            $customer_id = key($customer_list);
        }

        $customer->load($customer_id);

        if ($customer->isLoaded()) {
            $this->view->set('master_value', $customer->id);
            $this->view->set('company_id', $customer->company_id);
            $this->view->set('currency', $customer->currency);
            $this->view->set('payment_type', $customer->payment_type_id);
            $this->view->set('bank_account', $customer->cb_account_id);
        }

        $this->view->set('people', $this->getPeople($customer->company_id));

        $gl_account = DataObjectFactory::Factory('GLAccount');
        $gl_accounts = $gl_account->nonControlAccounts();
        $this->view->set('gl_accounts', $gl_accounts);

        if (isset($this->_data['glaccount_id'])) {
            $default_glaccount_id = $this->_data['glaccount_id'];
        } else {
            $default_glaccount_id = key($gl_accounts);
        }
        $this->view->set('centres', $this->getCentres($default_glaccount_id));

        $this->sidebar(__FUNCTION__);
    }

    public function save_journal()
    {
        $flash = Flash::Instance();
        $errors = array();
        $result = false;
        $data = $this->_data['SLTransaction'];
        
        $gl_account = DataObjectFactory::Factory('GLAccount');
        $allowed_accounts = $gl_account->nonControlAccounts();
        
        $post_allowed = array_key_exists($data['glaccount_id'], $allowed_accounts);
        if (!$post_allowed){
            $errors[] = 'Cannot post journal to a control account';
        }
        
        if ($this->checkParams('SLTransaction') && $post_allowed) {
            if ($data['net_value'] != 0) {
                $customer = $this->getCustomer($data['slmaster_id']);

                $data['currency_id'] = $customer->currency_id;
                $data['payment_term_id'] = $customer->payment_term_id;

                $db = DB::Instance();
                $data['our_reference'] = $db->GenID('sl_journals_id_seq');
                ;

                $result = SLTransaction::saveTransaction($data, $errors);

                if ($result !== false) {
                    $flash->addMessage('Journal saved');
                }
            } else {
                $errors[] = 'Zero value not allowed';
            }
        }
        if ($result !== false) {
            if (isset($this->_data['saveAnother'])) {
                $this->context['slmaster_id'] = $data['slmaster_id'];
                $this->saveAnother();
            }

            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $data['slmaster_id']
            ));
        }

        $flash->addErrors($errors);

        if (isset($data['slmaster_id']) && ! empty($data['slmaster_id'])) {
            $this->_data['slmaster_id'] = $data['slmaster_id'];
        }

        if (isset($data['glaccount_id']) && ! empty($data['glaccount_id'])) {
            $this->_data['glaccount_id'] = $data['glaccount_id'];
        }

        $this->refresh();
    }

    public function make_refund()
    {
        $this->cashbook_payment(__FUNCTION__);

        $this->view->set('type', 'RR');
    }

    public function receive_payment()
    {
        $this->cashbook_payment(__FUNCTION__);

        $this->view->set('type', 'R');
    }

    public function save_payment()
    {
        $flash = Flash::Instance();
        $errors = array();

        if (! $this->checkParams('CBTransaction')) {
            sendBack();
        }

        $data = $this->_data['CBTransaction'];

        if (isset($this->_data['SLTransaction']['slmaster_id'])) {
            $data['slmaster_id'] = $this->_data['SLTransaction']['slmaster_id'];

            $company = DataObjectFactory::Factory('SLCustomer');
            $company->load($data['slmaster_id']);

            if (! $company->isLoaded()) {
                $this->dataError('Cannot find Customer');
                sendBack();
            }

            $data['payment_term_id'] = $company->payment_term_id;
        }

        if (isset($this->_data['SLTransaction']['person_id'])) {
            $data['person_id'] = $this->_data['SLTransaction']['person_id'];
        }

        if ($data['net_value'] > 0) {

            $result = SLTransaction::saveTransaction($data, $errors);

            if ($result !== false) {
                $flash->addMessage('Payment saved');

                if (isset($this->_data['saveAnother'])) {
                    $this->context['slmaster_id'] = $data['slmaster_id'];
                    $this->saveAnother();
                }

                sendTo($this->name, 'view', $this->_modules, array(
                    'id' => $data['slmaster_id']
                ));
            } else {
                $errors[] = 'Error saving payment';
            }
        } else {
            $errors[] = 'Payment must be greater than zero';
        }

        $flash->addErrors($errors);

        if (isset($data['slmaster_id']) && ! empty($data['slmaster_id'])) {
            $this->_data['slmaster_id'] = $data['slmaster_id'];
        }

        $this->refresh();
    }

    public function periodicpayments()
    {
        sendTo('periodicpayments', 'index', 'cashbook', array(
            'source' => 'SR'
        ));
    }

    public function allocate()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }
        $customer = $this->_uses[$this->modeltype];

        $transaction = DataObjectFactory::Factory('SLTransaction');
        $transactions = new SLTransactionCollection($transaction, 'sl_allocation_overview');
        $sh = new SearchHandler($transactions, false);

        $db = DB::Instance();
        $sh->addConstraint(new Constraint('status', 'in', '(' . $db->qstr($transaction->open()) . ',' . $db->qstr($transaction->partPaid()) . ')'));
        $sh->addConstraint(new Constraint('slmaster_id', '=', $customer->id));

        $sh->setOrderby('transaction_date');
        $transactions->load($sh);

        $this->view->set('allocated_total', 0);

        $this->view->set('transactions', $transactions);
        $this->view->set('no_ordering', true);
    }

    public function save_allocation()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $db = DB::Instance();
        $db->StartTrans();

        $flash = Flash::Instance();

        $errors = array();

        $transactions = array();

        $allocated_total = 0.00;

        foreach ($this->_data['SLTransaction'] as $id => $data) {
            if (isset($data['allocate'])) {
                // using bcadd to format value
                $transactions[$id] = bcadd($data['os_value'], 0);
                $allocated_total = bcadd($allocated_total, $data['os_value']);
            }

            // Save settlement discount if present?
            if ($data['settlement_discount'] > 0 && isset($data['include_discount'])) {
                $transactions[$id] = bcadd($data['settlement_discount'], $transactions[$id]);
                // Create GL Journal for settlement discount

                // TODO: Check if need to create a SL transaction for the discount
                // and add id=>value pair to $transactions
                $sltransaction = DataObjectFactory::Factory('SLTransaction');

                $sltransaction->load($id);

                $discount = array();

                $discount['gross_value'] = $discount['net_value'] = $data['settlement_discount'];

                $discount['glaccount_id'] = $data['sl_discount_glaccount_id'];
                $discount['glcentre_id'] = $data['sl_discount_glcentre_id'];

                $discount['transaction_date'] = date(DATE_FORMAT);
                $discount['tax_value'] = '0.00';
                $discount['source'] = 'S';
                $discount['transaction_type'] = 'SD';
                $discount['our_reference'] = $sltransaction->our_reference;
                $discount['ext_reference'] = $sltransaction->ext_reference;
                $discount['currency_id'] = $sltransaction->currency_id;
                $discount['rate'] = $sltransaction->rate;
                $discount['description'] = (! empty($data['sl_discount_description']) ? $data['sl_discount_description'] . ' ' : '');
                $discount['description'] .= $sltransaction->description;
                $discount['payment_term_id'] = $sltransaction->payment_term_id;
                $discount['slmaster_id'] = $sltransaction->slmaster_id;

                $sldiscount = SLTransaction::Factory($discount, $errors, 'SLTransaction');

                if ($sldiscount && $sldiscount->save('', $errors) && $sldiscount->saveGLTransaction($discount, $errors)) {
                    $transactions[$sldiscount->{$sldiscount->idField}] = bcadd($discount['net_value'], 0);
                } else {
                    $errors[] = 'Errror saving SL Transaction Discount : ' . $db->ErrorMsg();
                    $flash->addErrors($errors);
                }
            }
        }

        if (count($transactions) == 0) {
            $flash->addError('You must select at least one transaction');
        } elseif (count($errors) == 0) {
            if (! SLTransaction::allocatePayment($transactions, $this->_data['id'], $errors) || ! SLAllocation::saveAllocation($transactions, $errors)) {
                $flash->addErrors($errors);
            } elseif ($db->CompleteTrans()) {
                $flash->addMessage('Transactions matched');
                sendTo($this->name, 'view', $this->_modules, array(
                    'id' => $this->_data['id']
                ));
            }
        }

        $db->FailTrans();
        $db->CompleteTrans();

        $this->allocate();

        $this->view->set('allocated_total', $allocated_total);

        $this->setTemplatename('allocate');
    }

    public function inquery_transactions()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];

        $transaction = DataObjectFactory::Factory('SLTransaction');
        $transactions = new SLTransactionCollection($transaction);

        $db = DB::Instance();

        $sh = $this->setSearchHandler($transactions);
        $sh->addConstraint(new Constraint('status', '=', $transaction->Query()));
        $sh->addConstraint(new Constraint('slmaster_id', '=', $customer->id));

        parent::index($transactions, $sh);

        $this->view->set('ledger_account', $customer);
        $this->view->set('collection', $transactions);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'sltransactions');
        $this->view->set('invoice_module', 'sales_invoicing');
        $this->view->set('invoice_controller', 'sinvoices');

        $this->_templateName = $this->getTemplateName('view_ledger_trans');
    }

    public function outstanding_transactions()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];

        $category = DataObjectFactory::Factory('LedgerCategory');
        $categories = $category->checkCompanyUsage($customer->company_id);

        // Check for Sales Ledger account and Contra Control account
        $glparams = DataObjectFactory::Factory('GLParams');

        // Does this SL Customer also have a PL Supplier account
        // and does the Contras Control Account also exist
        // if so, then allow contras
        $can_contra = (isset($categories['PL']['exists']) && $categories['PL']['exists'] && $glparams->contras_control_account() != FALSE);

        $this->view->set('can_contra', $can_contra);

        $transaction = DataObjectFactory::Factory('SLTransaction');
        $transactions = new SLTransactionCollection($transaction);

        $db = DB::Instance();

        $sh = $this->setSearchHandler($transactions);
        $sh->addConstraint(new Constraint('status', 'in', '(' . $db->qstr($transaction->open()) . ',' . $db->qstr($transaction->partPaid()) . ')'));
        $sh->addConstraint(new Constraint('slmaster_id', '=', $customer->id));

        parent::index($transactions, $sh);

        if ($can_contra) {
            // create session object to handle paged data input
            $contras_sessionobject = new SessionData('sl_contras');

            if (! $contras_sessionobject->PageDataExists()) {
                // session object does not exist so register it
                $contras_sessionobject->registerPageData(array(
                    'os_value',
                    'contra'
                ));
            }

            // Check for form input due to paging or ordering
            if (isset($this->_data['SLTransaction'])) {
                foreach ($this->_data['SLTransaction'] as $id => $fields) {
                    if ($fields['contra'] == 'on') {
                        $contras_sessionobject->updatePageData($id, $fields, $errors);
                    } else {
                        $contras_sessionobject->deletePageData($id);
                    }
                }
            }

            $contras_data = $contras_sessionobject->getPageData($errors);

            $contra_total = 0;

            foreach ($contras_data as $value) {
                if (isset($value['contra']) && $value['contra']) {
                    $contra_total += $value['os_value'];
                }
            }

            $this->view->set('contra_total', $contra_total);

            $this->view->set('page_data', $contras_data);
        }

        $this->view->set('ledger_account', $customer);
        $this->view->set('collection', $transactions);

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'sltransactions');
        $this->view->set('invoice_module', 'sales_invoicing');
        $this->view->set('invoice_controller', 'sinvoices');

        $this->_templateName = $this->getTemplateName('view_ledger_trans');
    }

    public function save_contras()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $db = DB::Instance();
        $db->StartTrans();

        $flash = Flash::Instance();

        $errors = array();

        $transactions = array();

        $contras_sessionobject = new SessionData('sl_contras');

        foreach ($this->_data['SLTransaction'] as $id => $data) {
            $data['contra'] = (isset($data['contra']) && $data['contra'] == 'on');
            $contras_sessionobject->updatePageData($id, $data, $errors);
        }

        $contra_total = (isset($this->_data['contra_total'])) ? $this->_data['contra_total'] : '0.00';

        $contra_sum = 0;

        foreach ($contras_sessionobject->getPageData($errors) as $id => $data) {
            if (isset($data['contra']) && $data['contra'] == 'on') {
                // using bcadd to format value
                $transactions[$id] = bcadd($data['os_value'], 0);
                $contra_sum = bcadd($contra_sum, $data['os_value']);
            }
        }

        if (count($transactions) == 0) {
            $errors[] = 'You must select at least one transaction';
        } elseif ($contra_total == $contra_sum) {
            $pl_journal_seq = $db->GenID('pl_journals_id_seq');
            $sl_journal_seq = $db->GenID('sl_journals_id_seq');

            // Create the PL and SL contra journals
            $sltransaction = DataObjectFactory::Factory('SLTransaction');

            $sltransaction->load($id);

            $slcontra = array();

            $slcontra['gross_value'] = $slcontra['net_value'] = bcmul($contra_sum, - 1);

            $glparams = DataObjectFactory::Factory('GLParams');

            $slcontra['glaccount_id'] = $glparams->contras_control_account();
            $slcontra['glcentre_id'] = $glparams->balance_sheet_cost_centre();

            $slcontra['transaction_date'] = date(DATE_FORMAT);
            $slcontra['tax_value'] = '0.00';
            $slcontra['source'] = 'S';
            $slcontra['transaction_type'] = 'J';
            $slcontra['our_reference'] = $sl_journal_seq;
            $slcontra['currency_id'] = $this->_data['SLCustomer']['currency_id'];
            $slcontra['rate'] = $this->_data['SLCustomer']['rate'];
            $slcontra['payment_term_id'] = $this->_data['SLCustomer']['payment_term_id'];

            $plcontra = $slcontra;

            $slcontra['slmaster_id'] = $this->_data['SLCustomer']['id'];
            $slcontra['description'] = 'Contra Sales Ledger - PL Ref:' . $pl_journal_seq;

            $sltrans = SLTransaction::Factory($slcontra, $errors, 'SLTransaction');

            if ($sltrans && $sltrans->save('', $errors) && $sltrans->saveGLTransaction($slcontra, $errors)) {
                $transactions[$sltrans->{$sltrans->idField}] = bcadd($slcontra['net_value'], 0);
            } else {
                $errors[] = 'Errror saving SL Transaction Contra : ' . $db->ErrorMsg();
                $flash->addErrors($errors);
            }

            $plcontra['source'] = 'P';
            $plcontra['our_reference'] = $pl_journal_seq;
            $plcontra['description'] = 'Contra Purchase Ledger - SL Ref:' . $sl_journal_seq;
            $plcontra['gross_value'] = $plcontra['net_value'] = bcmul($contra_sum, - 1);

            $supplier = DataObjectFactory::Factory('PLSupplier');
            $supplier->loadBy('company_id', $this->_data['SLCustomer']['company_id']);

            if ($supplier->isLoaded()) {
                $plcontra['plmaster_id'] = $supplier->{$supplier->idField};

                $pltrans = PLTransaction::Factory($plcontra, $errors, 'PLTransaction');
            } else {
                $pltrans = FALSE;
            }

            if (! $pltrans || ! $pltrans->save('', $errors) || ! $pltrans->saveGLTransaction($plcontra, $errors)) {
                $errors[] = 'Errror saving PL Transaction Contra : ' . $db->ErrorMsg();
                $flash->addErrors($errors);
            }
        } else {
            $errors[] = 'Transactions sum mismatch Sum: ' . $contra_sum . ' Control Total: ' . $contra_total;
        }

        if (count($errors) > 0 || ! SLTransaction::allocatePayment($transactions, $this->_data['id'], $errors) || ! SLAllocation::saveAllocation($transactions, $errors)) {
            $db->FailTrans();
        }

        if ($db->CompleteTrans()) {
            $contras_sessionobject->clear();

            $flash->addMessage('Contra Transactions matched');

            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->_data['id']
            ));
        }

        $flash->addErrors($errors);

        $this->outstanding_transactions();
    }

    public function all_transactions()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $customer = $this->_uses[$this->modeltype];

        $transactions = new SLTransactionCollection();

        $sh = $this->setSearchHandler($transactions);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $customer->id));

        parent::index($transactions, $sh);

        $this->view->set('collection', $transactions);
        $this->view->set('master_id', 'slmaster_id');

        $this->view->set('clickaction', 'view');
        $this->view->set('clickcontroller', 'sltransactions');
        $this->view->set('invoice_module', 'sales_invoicing');
        $this->view->set('invoice_controller', 'sinvoices');

        $this->_templateName = $this->getTemplateName('view_ledger_trans');
    }

    public function _new()
    {
        parent::_new();

        $customer = $this->_uses[$this->modeltype];

        if ($customer->isLoaded()) {
            $this->view->set('transaction_count', $customer->transaction_count());

            $emails = $this->getEmailAddresses($customer->company_id);

            unset($emails['']);

            $this->view->set('emails', $emails);
        } elseif (isset($this->_data['company_id'])) {
            $customer->company_id = $this->_data['company_id'];
        } else {
            $unassigned_list = $customer->getUnassignedCompanies();

            if (count($unassigned_list) > 0) {
                $this->view->set('company_list', $unassigned_list);

                $emails = $this->getEmailAddresses(key($unassigned_list));

                unset($emails['']);

                $this->view->set('emails', $emails);

                $customer->company_id = key($unassigned_list);
            } else {
                $flash = Flash::Instance();
                $flash->addMessage('All companies are assigned as customers');
                sendBack();
            }
        }

        $this->view->set('billing_addresses', $customer->getInvoiceAddresses());
        $this->view->set('despatch_actions', WHAction::getDespatchActions());
    }

    public function save()
    {
        $errors = array();

        $flash = Flash::Instance();

        $db = DB::Instance();

        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $company = DataObjectFactory::Factory('Company');
        $company->load($this->_data[$this->modeltype]['company_id']);

        if (! $company->isLoaded()) {
            $flash->addError('Invalid company');
            sendBack();
        }

        if ($this->_data[$this->modeltype]['email_invoice_id'] == 0) {
            $this->_data[$this->modeltype]['email_invoice_id'] = NULL;
        }

        if ($this->_data[$this->modeltype]['email_statement_id'] == 0) {
            $this->_data[$this->modeltype]['email_statement_id'] = NULL;
        }

        if (parent::save_model($this->modeltype, $this->_data[$this->modeltype], $errors)) {
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->saved_model->id
            ));
        } else {
            if (count($errors) > 0) {
                $flash->addErrors($errors);
            }
            $flash->addError('Error saving Customer ' . $db->ErrorMsg());
        }

        if (isset($this->_data[$this->modeltype]['id'])) {
            $this->_data['id'] = $this->_data[$this->modeltype]['id'];
        }

        $this->refresh();
    }

    /* output functions */
    public function printaction()
    {
        /*
         * If we're printing via the printAction function and calling 'print_customer_statement' function
         * then set the email and filename
         *
         * Otherwise we're probably printing via the
         */
        if (strtolower($this->_data['printaction']) == 'print_customer_statement') {
            if ($this->loadData()) {
                $customer = $this->_uses[$this->modeltype];
                $this->view->set('email', $customer->email_statement());
            }
            $this->_data['filename'] = 'Statement';
        }
        parent::printAction();
    }

    public function print_customer_statements($_customer_id, $_print_params)
    {
        $this->_data['id'] = $_customer_id;
        $this->_data['print'] = $_print_params;

        $response = json_decode($this->printStatement(), true);

        // bit paranoid about the data array being contaminated
        unset($this->_data['id'], $this->_data['print']);

        return $response;
    }

    public function printStatement($status = 'generate')
    {

        // Set the time limit on entry - may be one of a batch of statements
        set_time_limit(30);

        $customer = DataObjectFactory::Factory('SLCustomer');
        $customer->load($this->_data['id']);
        $bank_account = $customer->bank_account_detail;

        /*
         * if (SYSTEM_COMPANY<>'') {
         * $data['subject']='Statement from '.SYSTEM_COMPANY;
         * }
         */

        // set options
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
            'report' => 'Statement',
            'filename' => 'Statements_' . fix_date(date(DATE_FORMAT))
        );

        if (strtolower($this->_data['printaction']) == 'printstatement' && ! is_null($customer->email_statement())) {
            $options['default_print_action'] = 'email';
            $options['email'] = $customer->email_statement();
            $options['email_subject'] = 'Statement';
        }

        // if we're dealing with the dialog, just return the options...
        if (strtolower($status) == 'dialog') {
            return $options;
        }

        // ...otherwise continue with the function

        $sh = new SearchHandler(new SLTransactionCollection(), false);
        $sh->addConstraint(new Constraint('status', '<>', 'P'));
        $sh->setOrderby(array(
            'due_date'
        ), array(
            'ASC'
        ));
        $customer->addSearchHandler('transactions', $sh);

        $extra = array();

        // get the company address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());
        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // get the invoice address
        $payment_address = array();
        $payment_address['name'] = $customer->name;
        $payment_address += $this->formatAddress($customer->getBillingAddress());
        $extra['payment_address'] = $payment_address;

        // get current date
        $extra['current_date'] = un_fix_date(fix_date(date(DATE_FORMAT)));

        // get aged debtor summary
        foreach ($customer->getAgedDebtorSummary(3) as $key => $value) {
            $extra['aged_debtor_summary'][]['line'] = $value;
        }

        // get bank details
        if (! is_null($bank_account->bank_account_number)) {
            $extra['bank_account']['bank_name'] = $bank_account->bank_name;
            $extra['bank_account']['bank_sort_code'] = $bank_account->bank_sort_code;
            $extra['bank_account']['bank_account_number'] = $bank_account->bank_account_number;
            $extra['bank_account']['bank_address'] = $bank_account->bank_address;
            $extra['bank_account']['bank_iban_number'] = $bank_account->bank_iban_number;
            $extra['bank_account']['bank_bic_code'] = $bank_account->bank_bic_code;
        }

        // generate the XML, include the extras array too
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $customer,
            'extra' => $extra,
            'relationship_whitelist' => array(
                'transactions'
            )
        ));

        // construct the document, capture the response
        $json_response = $this->constructOutput($this->_data['print'], $options);

        // decode response, if it was successful update the print count
        $response = json_decode($json_response, true);
        if ($response['status'] === true) {
            if (! $customer->update($customer->id, 'last_statement_date', fix_date(date(DATE_FORMAT)))) {
                // if we cannot update the date, update json_responce with an error
                $json_response = $this->returnResponse(false, array(
                    'message' => 'Statement output correctly, but failed to update statement date for customer ' . $customer->name
                ));
            }
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

    /* others */
    public function viewcontact_methods()
    {
        if (! $this->checkParams('id')) {
            sendBack();
        }

        $flash = Flash::Instance();

        $errors = array();

        $customer = $this->_uses[$this->modeltype];

        $customer->load($this->_data['id']);

        $cc = new ConstraintChain();
        $cc->add(new Constraint('billing', 'is', true));

        $this->view->set('contactdetails', $customer->companydetail->getContactMethods('', $cc));
    }

    public function updatestatus()
    {
        if (! $this->checkParams(array(
            'id',
            'account_status'
        ))) {
            sendBack();
        }

        $flash = Flash::Instance();

        $customer = $this->_uses[$this->modeltype];

        $customer->load($this->_data['id']);

        if ($customer && $customer->update($this->_data['id'], 'account_status', $this->_data['account_status'])) {
            $flash->addMessage('Account Status updated');
        } else {
            $flash->addError('Account Status update failed');
        }

        // If we're changing to stopped status, show the new Party Note form
        if (!$customer->accountStopped())
        {
            sendTo('partynotes', 'new', 'contacts', array(
                'party_id' => $customer->getPartyID(), 'title' => 'Account stopped', 'note_type' => 'contacts'
            ));
        } else {
            sendTo($this->name, 'view', $this->_modules, array(
                'id' => $this->_data['id']
            ));
        }
    }

    /*
     * Ajax Functions
     */
    public function getbankAccountId($_id = '')
    {
        // Used by Ajax to return bank account id after selecting the Customer
        $value = '';

        $customer = $this->getCustomer($_id);

        if ($customer->isLoaded()) {
            $value = $customer->cb_account_id;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $value);
            $this->setTemplateName('text_inner');
        } else {
            return $value;
        }
    }

    public function getbankAccounts($_customer_id = '')
    {
        // Used by Ajax to return list of allowed bank accounts after selecting the Customer
        $cbaccounts = array();
        $cbaccount_id = '';

        $customer = $this->getCustomer($_customer_id);

        if ($customer->isLoaded()) {
            $currency_id = $customer->currency_id;
            $cbaccount_id = $customer->cb_account_id;

            $cc = new ConstraintChain();

            $glparams = DataObjectFactory::Factory('GLParams');
            $base_currency_id = $glparams->base_currency();

            if ($currency_id != $base_currency_id) {
                $cc->add(new Constraint('currency_id', 'in', '(' . $currency_id . ',' . $base_currency_id . ')'));
            }

            $cbaccount = DataObjectFactory::Factory('CBAccount');

            $cbaccounts = $cbaccount->getAll($cc);
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $cbaccount_id);
            // $this->view->set('options',$cbaccounts);
            // return $this->view->fetch('select_options');
            return $cbaccounts;
        } else {
            return $cbaccounts;
        }
    }

    public function getInvoiceAddresses($_company_id = '')
    {
        // Used by Ajax to return Payment Type after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_company_id = $this->_data['id'];
            }
        }

        $addresses = array();

        if (! empty($_company_id)) {
            $customer = $this->_uses[$this->modeltype];

            $customer->company_id = $_company_id;

            $addresses = $customer->getInvoiceAddresses();
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $addresses);
            $this->setTemplateName('select_options');
        } else {
            return $addresses;
        }
    }

    public function getCentres($_id = '')
    {
        // Used by Ajax to return GL Cost Centre after selecting the GL Account
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $centres = $this->_templateobject->getCentres($_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $centres);
            $this->setTemplateName('select_options');
        } else {
            return $centres;
        }
    }

    public function getCurrencyId($_id = '')
    {
        // Used by Ajax to return Currency after selecting the Customer
        $currency = '';

        $customer = $this->getCustomer($_id);

        if ($customer->isLoaded()) {
            $currency = $customer->currency_id;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $currency);
            $this->setTemplateName('text_inner');
        } else {
            return $currency;
        }
    }

    public function getCurrency($_id = '')
    {
        // Used by Ajax to return Currency after selecting the Customer
        $currency = '';

        $customer = $this->getCustomer($_id);

        if ($customer->isLoaded()) {
            $currency = $customer->currency;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $currency);
            $this->setTemplateName('text_inner');
        } else {
            return $currency;
        }
    }

    public function getAccountRate($_customer_id = '', $_cb_account_id = '')
    {
        // Used by Ajax to return Currency after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_customer_id = $this->_data['id'];
            }
            if (! empty($this->_data['cb_account_id'])) {
                $_cb_account_id = $this->_data['cb_account_id'];
            }
        }

        $rate = '';

        $glparams = DataObjectFactory::Factory('GLParams');

        $customer = $this->getCustomer($_customer_id);

        if ($customer->isLoaded() && $glparams->base_currency() != $customer->currency_id) {
            $rate = $customer->currency_detail->rate;
        }

        if (empty($rate) && ! empty($_cb_account_id)) {

            $cb_account = DataObjectFactory::Factory('CBAccount');
            $cb_account->load($_cb_account_id);

            if ($cb_account->isLoaded() && $glparams->base_currency() != $cb_account->currency_id) {
                $rate = $cb_account->currency_detail->rate;
            }
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $rate);
            $this->setTemplateName('text_inner');
        } else {
            return $rate;
        }
    }

    public function getEmailAddresses($_id = '')
    {
        // Used by Ajax to return Email Addresses after selecting the Customer
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $emails = array(
            '' => 'None'
        );

        if (! empty($_id)) {
            $company = DataObjectFactory::Factory('Company');
            $company->load($_id);
            if ($company->isLoaded()) {
                foreach ($company->getEmailAddresses() as $emailaddresses) {
                    $emails[$emailaddresses->id] = $emailaddresses->contact;
                }
            }
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $emails);
            $this->setTemplateName('select_options');
        } else {
            return $emails;
        }
    }

    public function getPaymentTypeId($_id = '')
    {
        // Used by Ajax to return Currency after selecting the Customer
        $payment_type = '';

        $customer = $this->getCustomer($_id);

        if ($customer->isLoaded()) {
            $payment_type = $customer->payment_type_id;
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $payment_type);
            $this->setTemplateName('text_inner');
        } else {
            return $payment_type;
        }
    }

    public function getCustomerData()
    {
        // this function will only ever be called via an AJAX request, no paramters needed
        if (isset($this->_data['ajax'])) {
            $this->_data['ajax_call'] = '';
        }

        $fields = explode(',', $this->_data['fields']);

        $customer = $this->getCustomer($this->_data['id']);

        foreach ($fields as $key => $value) {
            $temp = $customer->$value;
            $output[$value] = array(
                'data' => $temp,
                'is_array' => is_array($temp)
            );
        }

        $accounts = $this->getBankAccounts($customer->id);
        $output['cb_account_id'] = array(
            'data' => $accounts,
            'is_array' => is_array($accounts)
        );

        $people = $this->getPeople($customer->company_id);
        $output['person_id'] = array(
            'data' => $people,
            'is_array' => is_array($people)
        );

        $this->view->set('data', $output);
        $this->setTemplateName('ajax_multiple');
    }

    public function getPeople($_company_id = '')
    {
        if (empty($_company_id) && isset($this->_data['company_id'])) {
            $_company_id = $this->_data['company_id'];
        }

        if (empty($_company_id) && isset($this->_data['master_id'])) {
            $customer = $this->getCustomer($this->_data['master_id']);
            $_company_id = $customer->company_id;
        }

        $cc = new ConstraintChain();

        if (! empty($_company_id)) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('company_id', '=', $_company_id));
            $this->_uses['SLTransaction']->belongsTo[$this->_uses['SLTransaction']->belongsToField['person_id']]['cc'] = $cc;
        }

        $smarty_params = array(
            'nonone' => 'true',
            'depends' => 'slmaster_id'
        );
        unset($this->_data['depends']);

        return $this->getOptions($this->_uses['SLTransaction'], 'person_id', 'getPeople', 'getOptions', $smarty_params, $depends);
    }

    /*
     * Private Functions
     */
    private function cashbook_payment($current_type = __FUNCTION__)
    {
        $customer = $this->_uses[$this->modeltype];

        $customer_list = $this->getCustomerList();

        if (isset($this->_data['slmaster_id'])) {
            $customer_id = $this->_data['slmaster_id'];
        } else {
            $customer_id = key($customer_list);
        }

        $customer->load($customer_id);

        if (! $customer->isLoaded()) {
            $flash = Flash::Instance();
            $flash->addError('Error loading Customer details');
            sendBack();
        }

        $this->_data['currency_id'] = $customer->currency_id;

        $this->view->set('master_value', $customer_id);
        $this->view->set('company_id', $customer->company_id);
        $this->view->set('people', $this->getPeople($customer->company_id));
        $this->view->set('currency', $customer->currency);
        $this->view->set('payment_type', $customer->payment_type_id);

        if (is_null($customer->cb_account_id)) {
            $cbaccount = CBAccount::getPrimaryAccount();
            $customer->cb_account_id = $cbaccount->{$cbaccount->idField};
        }

        $this->view->set('bank_account', $customer->cb_account_id);
        $this->view->set('bank_accounts', $this->getbankAccounts($customer->id));
        $this->view->set('rate', $this->getAccountRate($customer->id, $customer->cb_account_id));
        $this->view->set('companies', $customer_list);

        $this->sidebar($current_type);
        $this->_templateName = $this->getTemplateName('enter_payment');
    }

    private function getCustomer($_customer_id = '')
    {
        $customer = $this->_uses[$this->modeltype];

        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['slmaster_id'])) {
                $_customer_id = $this->_data['slmaster_id'];
            }
        }

        if (! empty($_customer_id)) {
            if ($customer->isLoaded()) {
                $customer = DataObjectFactory::Factory('SLCustomer');
            }

            $customer->load($_customer_id);
        } elseif (! $customer->isLoaded()) {
            $this->loadData();

            $customer = $this->_uses[$this->modeltype];
        }

        return $customer;
    }

    private function sidebar($current_type)
    {
        $this->view->set('source', 'S');

        $this->view->set('Transaction', $this->_uses['SLTransaction']);
        $this->view->set('master_id', 'slmaster_id');
        $this->view->set('master_label', 'Customer');

        $sidebar = new SidebarController($this->view);

        $sidebarlist = array();

        $sidebarlist['all'] = array(
            'tag' => 'View all customers',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index'
            )
        );

        if ($current_type != 'receive_payment') {
            $sidebarlist['receive_payment'] = array(
                'tag' => 'receive_payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'receive_payment'
                )
            );
        }

        if ($current_type != 'make_refund') {
            $sidebarlist['make_refund'] = array(
                'tag' => 'make_refund',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_refund'
                )
            );
        }

        if ($current_type != 'enter_journal') {
            $sidebarlist['enter_journal'] = array(
                'tag' => 'Enter SL Journal',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'enter_journal'
                )
            );
        }

        $sidebar->addList('Actions', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    /*
     * Protected Functions
     */
    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'sales_ledger_customers' : $base), $type);
    }
}

// End of SlcustomersController
