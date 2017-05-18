<?php

/**
 *	HR Expenses Controller
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
class ExpensesController extends HrController
{

    protected $version = '$Revision: 1.33 $';

    protected $_templateobject;

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('Expense');
        $this->uses($this->_templateobject);
        $this->view->set('controller', 'Expenses');
    }

    public function index()
    {
        $errors = array();

        $s_data = array();

        // Set context from calling module
        $this->setSearch('expenseSearch', 'useDefault', $s_data);

        $this->view->set('clickaction', 'view');

        parent::index(new ExpenseCollection($this->_templateobject));

        $sidebarlist = array();

        $sidebarlist['new'] = array(
            'tag' => 'New',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'new'
            )
        );

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('actions', $sidebarlist);

        $this->view->register('sidebar', $sidebar);

        $this->view->set('sidebar', $sidebar);
    }

    public function confirmRequest()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $expense = $this->_uses[$this->modeltype];

        if ($expense->gross_value == 0) {
            $this->dataError('Expenses value is zero');
            sendBack();
        }

        $employee = DataObjectFactory::Factory('Employee');

        $employee->load($expense->employee_id);

        $this->view->set('current_status', $expense->getFormatted('status'));

        $expense->status = $expense->authorised();

        $this->view->set('employee', $employee);

        $this->view->set('authoriser', $this->get_employee_id());
        $this->view->set('Expense', $expense);
    }

    public function confirmSaveRequest()
    {
        $flash = Flash::Instance();

        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        $errors = array();

        $this->_data[$this->modeltype]['authorised_date'] = date(DATE_FORMAT);

        if (! Expense::updateStatus($this->_data[$this->modeltype], $errors)) {
            $flash->addErrors($errors);
        } else {
            $flash->addMessage('Expenses updated OK');
        }

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function delete()
    {
        $flash = Flash::Instance();
        if (! $this->checkParams('id')) {
            sendBack();
        }

        $expense = DataObjectFactory::Factory('Expense');

        $expense->update($this->_data['id'], 'status', $expense->cancel());

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function _new()
    {
        $flash = Flash::Instance();

        parent::_new();

        $expense = $this->_uses[$this->modeltype];

        if ($expense->isLoaded()) {
            $this->_data['employee_id'] = $expense->employee_id;
        }

        if (isset($this->_data['employee_id'])) {
            $employee = DataObjectFactory::Factory('Employee');
            $employee->load($this->_data['employee_id']);

            if (! $employee->isLoaded()) {
                $flash->addError('You cannot set up an expense claim for this person');
                sendBack();
            }

            $this->view->set('title', ' for ' . $employee->person->getIdentifierValue());
        }

        $params = DataObjectFactory::Factory('GLParams');
        $this->view->set('base_currency', $params->base_currency());

        $accounts = DataObjectFactory::Factory('GLAccount');
        $accounts = $accounts->getAll();
        $this->view->set('accounts', $accounts);

        $tax_rates = DataObjectFactory::Factory('TaxRate');
        $tax_rates = $tax_rates->getAll();
        $this->view->set('taxrates', $tax_rates);

        if (! $expense->isLoaded() && ! empty($this->_data['project_id'])) {
            $expense->project_id = $this->_data['project_id'];
        }

        $this->view->set('tasks', $this->getTaskList($expense->project_id));

        $this->view->set('employee', $employee);
    }

    public function new_expense()
    {
        $this->_data['employee_id'] = $this->get_employee_id();

        $this->_new();

        $this->_templateName = $this->getTemplateName('new');
    }

    public function view_my_expenses()
    {
        $flash = Flash::Instance();

        $employee_id = $this->get_employee_id();

        if (! empty($employee_id)) {

            $this->_templateName = $this->getTemplateName('index');

            $errors = array();

            $s_data = array();

            // Set context from calling module
            $this->setSearch('expenseSearch', 'myExpenses', $s_data);

            unset($this->_templateobject->defaultDisplayFields['person']);

            $he = new ExpenseCollection($this->_templateobject);

            $sh = new SearchHandler($he, false);

            $sh->extract();

            $sh->addConstraint(new Constraint('employee_id', '=', $employee_id));

            parent::index($he, $sh);

            $sidebarlist = array();

            $sidebarlist['all_expenses'] = array(
                'tag' => 'View All Expenses',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'index',
                )
            );

            $sidebarlist['new'] = array(
                'tag' => 'New',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'new',
                    'employee_id' => $employee_id
                )
            );

            $sidebar = new SidebarController($this->view);

            $sidebar->addList('actions', $sidebarlist);

            $this->view->register('sidebar', $sidebar);

            $this->view->set('sidebar', $sidebar);

            $this->view->set('clickaction', 'view');
        } else {
            $flash->addError('You have not been set up as an employee');
            sendBack();
        }
    }

    public function save()
    {
        $flash = Flash::Instance();

        $errors = array();

        if (! $this->checkParams($this->modeltype)) {
            sendBack();
        }

        if (parent::save($this->modeltype, '', $errors)) {
            if (strtolower($this->_data['saveform']) == 'save and post') {
                $this->_data['id'] = $this->_data[$this->modeltype]['id'];
                $this->post();
            } else {
                sendTo($this->name, 'view', $this->_modules, array(
                    'id' => $this->saved_model->id
                ));
            }
        } else {
            if (! empty($this->_data[$this->modeltype]['id'])) {
                $this->_data['id'] = $this->_data[$this->modeltype]['id'];
            }

            $flash->addErrors($errors);

            $this->refresh();
        }
    }

    public function make_payment()
    {
        if (! $this->loadData()) {
            $this->dataError('Error loading expense claim');
            sendBack();
        }

        $expense = $this->_uses[$this->modeltype];

        $employee = DataObjectFactory::Factory('Employee');
        // If we are here, then need access to employee
        // by overriding any policy constraints
        $employee->clearPolicyConstraint();
        $employee->load($expense->employee_id);

        $this->view->set('person', $employee->employee);

        $account = DataObjectFactory::Factory('CBAccount');
        $accounts = $account->getAll();
        $this->view->set('accounts', $accounts);

        if (isset($this->_data['cb_account_id'])) {
            $default_account = $this->_data['cb_account_id'];

            $account->load($default_account);

            $this->view->set('account', $account->name);
        } else {
            $account->getDefaultAccount(key($accounts));

            $default_account = $account->{$account->idField};
        }

        $this->view->set('account_id', $default_account);

        $cbtransaction = DataObjectFactory::Factory('CBTransaction');

        $cbtransaction->person_id = $employee->person_id;
        $cbtransaction->currency_id = $expense->currency_id;
        $cbtransaction->net_value = $expense->gross_value;

        $this->view->set('Expense', $expense);
        $this->view->set('CBTransaction', $cbtransaction);

        $this->view->set('company', SYSTEM_COMPANY);

        $this->view->set('company_id', COMPANY_ID);

        $this->view->set('rate', $this->getAccountCurrencyRate($default_account, $account->currency_id));
    }

    public function savepayment()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $errors = array();

        $expense = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        if ($expense->isLoaded() && $expense->pay_claim($this->_data['CBTransaction'], $errors)) {
            $flash->addMessage('Expense Claim paid OK');
        } else {
            $flash->addErrors($errors);
            $flash->addError('Failed to pay Expense Claim');
        }

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function post()
    {
        if (! $this->loadData()) {
            $this->dataError();
            sendBack();
        }

        $errors = array();

        $expense = $this->_uses[$this->modeltype];

        $flash = Flash::Instance();

        if ($expense->isLoaded() && $expense->post($errors)) {
            $flash->addMessage('Expenses posted OK');
        } else {
            $flash->addErrors($errors);
            $flash->addError('Failed to post Expenses');
        }

        sendTo($_SESSION['refererPage']['controller'], $_SESSION['refererPage']['action'], $_SESSION['refererPage']['modules'], isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
    }

    public function print_expense($status = 'generate')
    {

        // load the model
        $expense = $this->_uses[$this->modeltype];
        $expense->load($this->_data['id']);

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
            'filename' => 'Expense-' . $expense->expense_number,
            'report' => 'expenses'
        );

        if (strtolower($status) == "dialog") {

            // show the main dialog
            // pick up the options from above, use these to shape the dialog

            return $options;
        }

        /* generate document */

        // get the original data
        // $saved_data = $this->decode_original_form_data($this->_data['encoded_query_data']);

        // construct extra array
        $extra = array();

        // load the employee
        $employee = DataObjectFactory::Factory('Employee');
        $employee->authorisationPolicy($employee->expense_model());
        $employee->load($expense->employee_id);

        // load the employee
        if (! is_null($expense->authorised_by)) {
            $authoriser = DataObjectFactory::Factory('Employee');

            $authoriser->load($expense->authorised_by);

            $extra['authoriser'] = $authoriser->person->getIdentifierValue();
        } else {
            $extra['authoriser'] = '';
        }

        // get employee address
        $employee_address = array(
            'employee' => $employee->person->titlename
        );

        $employee_address += $this->formatAddress($employee->personal_address);

        $extra['employee_address'] = $employee_address;

        // get company_address address
        $company_address = array(
            'name' => $this->getCompanyName()
        );
        $company_address += $this->formatAddress($this->getCompanyAddress());

        $extra['company_address'] = $company_address;

        // get the company details
        $extra['company_details'] = $this->getCompanyDetails();

        // Set variables for expense values
        $net_total = 0;
        $vat_total = 0;
        $inv_total = 0;

        $vat_analysis = '';
        $vat_rate = '';
        $vat_amount = '';
        $net_amount = '';

        $taxrate = array();
        $analysis = array();

        foreach ($expense->lines as $expenselines) {

            // Construct totals for generic info
            $net_total = bcadd($expenselines->net_value, $net_total);
            $vat_total = bcadd($expenselines->tax_value, $vat_total);

            // Construct array for summary vat info
            if (isset($taxrate[$expenselines->tax_rate_id])) {
                $taxrate[$expenselines->tax_rate_id]['line']['net_value'] += $expenselines->net_value;
                $taxrate[$expenselines->tax_rate_id]['line']['tax_value'] += $expenselines->tax_value;
            } else {
                $rate = DataObjectFactory::Factory('TaxRate');
                $rate->load($expenselines->tax_rate_id);

                $taxrate[$expenselines->tax_rate_id]['line']['description'] = $rate->description;
                $taxrate[$expenselines->tax_rate_id]['line']['currency'] = $expenselines->currency;
                $taxrate[$expenselines->tax_rate_id]['line']['tax_rate'] = $rate->percentage;
                $taxrate[$expenselines->tax_rate_id]['line']['net_value'] = $expenselines->net_value;
                $taxrate[$expenselines->tax_rate_id]['line']['tax_value'] = $expenselines->tax_value;
            }

            if (isset($analysis[$expenselines->glaccount_id])) {
                $analysis[$expenselines->glaccount_id]['line']['value'] += $expenselines->net_value;
            } else {
                $analysis[$expenselines->glaccount_id]['line']['account'] = $expenselines->glaccount;
                $analysis[$expenselines->glaccount_id]['line']['value'] = $expenselines->net_value;
            }
        }

        $extra['analysis'] = $analysis;

        $extra['vat_analysis'] = $taxrate;

        // Set invoice total
        $expense_total = bcadd($net_total, $vat_total);

        // get expense totals
        $expense_totals = array();

        $expense_totals[]['line'] = array(
            'label' => 'NET VALUE',
            'value' => number_format($net_total, 2, '.', '') . ' ' . $expense->currency
        );
        $expense_totals[]['line'] = array(
            'label' => 'VAT',
            'value' => number_format($vat_total, 2, '.', '') . ' ' . $expense->currency
        );
        $expense_totals[]['line'] = array(
            'label' => 'EXPENSE TOTAL',
            'value' => number_format($expense_total, 2, '.', '') . ' ' . $expense->currency
        );

        $extra['expense_totals'] = $expense_totals;

        // generate the xml and add it to the options array
        $options['xmlSource'] = $this->generateXML(array(
            'model' => $expense,
            'extra' => $extra,
            'relationship_whitelist' => array(
                'lines'
            )
        ));

        // execute the print output function, echo the returned json for jquery
        echo $this->constructOutput($this->_data['print'], $options);
        exit();
    }

    public function view()
    {
        if (! $this->loadData() && ! isset($this->_data['expense_number'])) {
            $this->dataError();
            sendBack();
        }

        $expense = $this->_uses[$this->modeltype];

        if (! $expense->isLoaded()) {
            if (isset($this->_data['expense_number'])) {
                $cc = new ConstraintChain();
                $cc->add(new Constraint('expense_number', '=', $this->_data['expense_number']));
                $expense->loadBy($cc);
            }
            if (! $expense->isLoaded()) {
                $flash->addError('Failed to find Expense');
                sendBack();
            }
        }

        $employee = DataObjectFactory::Factory('Employee');
        // Override the employee policy for authorisors
        $employee->authorisationPolicy($employee->expense_model());

        $employee->load($expense->employee_id);

        $this->view->set('employee', $employee);

        $idField = $expense->idField;
        $idValue = $expense->{$expense->idField};

        $sidebarlist = array();

        $sidebarlist[$expense->employee_id] = array(
            'tag' => 'View My Expenses',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'view_my_expenses',
            )
        );

        $sidebarlist['all_expenses'] = array(
            'tag' => 'View All Expenses',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'index',
            )
        );

        $sidebarlist['print_expense_claim'] = array(
            'tag' => 'print expense claim',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => $this->name,
                'action' => 'printDialog',
                'printaction' => 'print_expense',
                $idField => $idValue
            ),
            'class' => 'related_link'
        );

        if ($expense->awaitingAuthorisation() || ($expense->authorised() && $expense->employee_id != $this->get_employee_id())) {
            $sidebarlist['cancel'] = array(
                'tag' => 'Cancel Expenses',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'delete',
                    $idField => $idValue
                )
            );
        }

        if ($expense->awaitingAuthorisation()) {
            $sidebarlist['edit'] = array(
                'tag' => 'Amend Expenses',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'edit',
                    $idField => $idValue
                )
            );
            $sidebarlist['add_lines'] = array(
                'tag' => 'Add_Lines',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'expenselines',
                    'action' => 'new',
                    'expenses_header_id' => $idValue
                )
            );
        }

        if ($expense->Authorised()) {
            $sidebarlist['post'] = array(
                'tag' => 'Post to Ledger',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'post',
                    $idField => $idValue
                )
            );
        }

        if ($expense->hasBeenPosted()) {
            $sidebarlist['post'] = array(
                'tag' => 'Pay this Expense Claim',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_payment',
                    $idField => $idValue
                )
            );
        }

        // Expenses can only be authorised by an 'authoriser'
        if ($employee->expense_model()->isAuthorised($employee->id, $this->get_employee_id()) && $expense->awaitingAuthorisation() && $expense->gross_value != 0) {
            $sidebarlist['confirm'] = array(
                'tag' => 'Authorise Expenses',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'confirmRequest',
                    $idField => $idValue
                )
            );
        }

        $sidebar = new SidebarController($this->view);

        $sidebar->addList('currently_viewing', $sidebarlist);

        $this->view->register('sidebar', $sidebar);

        $this->view->set('sidebar', $sidebar);
    }

    /*
     * Protected functions
     */

    /*
     * Private functions
     */
    private function getAccountCurrencyRate($_cb_account_id = '', $_currency_id = '')
    {
        $rate = '';

        $glparams = DataObjectFactory::Factory('GLParams');

        if (! empty($_currency_id) && $_currency_id != $glparams->base_currency()) {
            $currency = DataObjectFactory::Factory('Currency');
            $currency->load($_currency_id);
            $rate = $currency->rate;
        }

        if (empty($rate) && ! empty($_cb_account_id)) {
            $cbaccount = DataObjectFactory::Factory('CBAccount');
            $cbaccount->load($_cb_account_id);
            if ($cbaccount->currency_id != $glparams->base_currency()) {
                $rate = $cbaccount->currency_detail->rate;
            }
        }

        return $rate;
    }

    /*
     * Ajax functions
     */
    public function getCurrencyRate($_cb_account_id = '', $_currency_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['cb_account_id'])) {
                $_cb_account_id = $this->_data['cb_account_id'];
            }
            if (! empty($this->_data['currency_id'])) {
                $_currency_id = $this->_data['currency_id'];
            }
        }

        $rate = $this->getAccountCurrencyRate($_cb_account_id, $_currency_id);

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $rate);
            $this->setTemplateName('text_inner');
        } else {
            return $rate;
        }
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
}

// End of ExpensesController
