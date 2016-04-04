<?php

/**
 * Cashbook Transactions controller
 *
 * @package cashbook
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2000-2016 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class CbtransactionsController extends printController
{

    public function __construct($module = null, $action = null)
    {
        parent::__construct($module, $action);

        $this->_templateobject = DataObjectFactory::Factory('CBTransaction');

        $this->uses($this->_templateobject);
    }

    public function index()
    {
        $defaults = array();

        if (isset($this->_data['cb_account_id'])) {
            $defaults['cb_account_id'] = $this->_data['cb_account_id'];
            $defaults['reference'] = '';
        }

        $errors = array();

        $this->setSearch('cbtransactionsSearch', 'useDefault', $defaults);

        // Disable user display field selection, see: BaseSearch
        $this->search->disable_field_selection = TRUE;

        $this->view->set('clickaction', 'view');
        parent::index(new CBTransactionCollection($this->_templateobject));

        $sidebar = new SidebarController($this->view);

        if (isset($this->_data['cb_account_id'])) {
            $account = DataObjectFactory::Factory('CBAccount');
            $account->load($this->_data['cb_account_id']);
            $this->view->set('account', $account->name);
            $this->view->set('currency', $account->currency);
            $this->sidebar($sidebar, $account);
        } else {
            $this->sidebar($sidebar);
        }

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function view()
    {
        $transaction = $this->_uses[$this->modeltype];

        if (isset($this->_data['id'])) {
            $transaction->load($this->_data['id']);
        }

        if (isset($this->_data['reference'])) {
            $cc = new ConstraintChain();
            $cc->add(new Constraint('reference', '=', $this->_data['reference']));
            $transaction->loadBy($cc);
        }

        $id = $transaction->{$transaction->idField};

        $this->view->set('transaction', $transaction);

        $account = DataObjectFactory::Factory('CBAccount');
        $account->load($transaction->cb_account_id);

        $sidebar = new SidebarController($this->view);

        $this->sidebar($sidebar, $account);

        $sidebarlist = array();

        $sidebarlist['gltransaction'] = array(
            'tag' => 'View GL Transaction',
            'link' => array(
                'module' => 'general_ledger',
                'controller' => 'gltransactions',
                'action' => 'index',
                'docref' => $transaction->reference,
                'source' => $transaction->source,
                'type' => $transaction->type
            )
        );

        if ($transaction->allow_refund()) {
            $sidebarlist['refund'] = array(
                'tag' => 'Process Refund',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_refund',
                    'id' => $id
                )
            );
        }

        $sidebar->addList('This transaction', $sidebarlist);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);
    }

    public function make_payment()
    {
        $this->payments(__FUNCTION__);
    }

    public function make_refund()
    {
        if (! $this->loadData()) {
            $this->dataError('Unable to load transaction');
            sendBack();
        }

        $transaction = $this->_uses[$this->modeltype];

        $transaction->transaction_date = fix_date(date(DATE_FORMAT));

        if ($transaction->net_value < 0) {
            $transaction->net_value = bcmul($transaction->net_value, - 1);
            $transaction->tax_value = bcmul($transaction->tax_value, - 1);
            $transaction->gross_value = bcmul($transaction->gross_value, - 1);
        }

        $this->view->set('page_title', 'Make Cash Book ' . $transaction->getFormatted('type') . ' Refund');

        $transaction->type = 'R' . $transaction->type;
        $transaction->description = 'Refund for ref: ' . $transaction->reference;

        $gltransaction = DataObjectFactory::Factory('GLTransaction');

        $gltransaction->loadBy('docref', $transaction->reference);
        $gltransaction->orderby = $gltransaction->idField;

        $this->_data['glaccount_id'] = $gltransaction->glaccount_id;

        $this->payments();

        $this->view->set('glaccount_id', $gltransaction->glaccount_id);
        $this->view->set('glcentre_id', $gltransaction->glcentre_id);
    }

    public function receive_payment()
    {
        $this->payments(__FUNCTION__);
    }

    public function save()
    {
        $flash = Flash::Instance();
        $errors = array();

        if (isset($this->_data[$this->modeltype])) {
            $data = $this->_data[$this->modeltype];
        } else {
            sendTo($this->name, 'index', $this->_modules);
        }

        if (! $data['person_id']) {
            unset($data['person_id']);
        }

        if ($data['type'] == 'P' || $data['type'] == 'RR') {
            $data['net_value'] = bcmul($data['net_value'], - 1);
            $data['tax_value'] = bcmul($data['tax_value'], - 1);
        }

        if (CBTransaction::saveCashPayment($data, $errors)) {
            $flash->addMessage($this->_templateobject->getEnum('type', $data['type']) . ' Saved');
            if (isset($this->_data['saveAnother'])) {
                $this->context['cb_account_id'] = $data['cb_account_id'];
                $this->saveAnother();
            }

            sendTo($this->name, 'index', $this->_modules);
        }

        $flash->addErrors($errors);
        $flash->addError('Error saving Payment');

        $this->_data['cb_account_id'] = $data['cb_account_id'];
        $this->_data['glaccount_id'] = $data['glaccount_id'];

        $this->refresh();
    }

    public function move_money()
    {
        $account = DataObjectFactory::Factory('CBAccount');
        $accounts = $account->getAll();
        $this->view->set('accounts', $accounts);

        if (isset($this->_data['cb_account_id'])) {
            $account->load($this->_data['cb_account_id']);

            $this->view->set('account_id', $account->id);
            $this->view->set('account', $account->name);
        } else {
            $account->getDefaultAccount(key($accounts));

            $account->id = $account->{$account->idField};
        }

        $sidebar = new SidebarController($this->view);

        $this->sidebar($sidebar, null, __FUNCTION__);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('currency', $account->currency);
        $this->view->set('currency_id', $account->currency_id);

        $to_accounts = $this->getOtherAccounts($account->id);

        if (isset($this->_data['to_account_id'])) {
            $to_account = $this->_data['to_account_id'];
        } else {
            $to_account = key($to_accounts);
        }

        $this->view->set('currencies', $this->getAccountCurrencies($account->id, $to_account));
        $this->view->set('to_accounts', $to_accounts);

        $this->view->set('rate', $this->getAccountRate($account->id, $to_account));
    }

    public function saveMovement()
    {
        $flash = Flash::Instance();
        $errors = array();

        $data = $this->_data['CBTransaction'];
        $trans = CBTransaction::moveMoney($data, $errors);

        if ($trans) {
            $flash->addMessage('Transfer completed');
            if (isset($this->_data['saveAnother'])) {
                $this->context['cb_account_id'] = $data['cb_account_id'];
                $this->saveAnother();
            }

            sendTo($this->name, 'index', $this->_modules);
        }

        $flash->addErrors($errors);
        $flash->addError('Error saving money transfer');

        $this->_data['cb_account_id'] = $data['cb_account_id'];
        $this->_data['to_account_id'] = $data['to_account_id'];

        $this->refresh();
    }

    /*
     * Private Functions
     */
    private function sidebar($sidebar, $account = null, $function = '')
    {
        $sidebarlist = array();

        $sidebarlist['viewaccounts'] = array(
            'tag' => 'View All Accounts',
            'link' => array(
                'modules' => $this->_modules,
                'controller' => 'bankaccounts',
                'action' => 'index'
            )
        );

        if ($function != 'receive_payment') {
            $sidebarlist['receivepaymentall'] = array(
                'tag' => 'Receive Payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'receive_payment'
                )
            );
        }

        if ($function != 'make_payment') {
            $sidebarlist['makepaymentall'] = array(
                'tag' => 'Make Payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_payment'
                )
            );
        }

        if ($function != 'move_money') {
            $sidebarlist['movemoneyall'] = array(
                'tag' => 'Move Money',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'move_money'
                )
            );
        }

        $sidebar->addList('Actions', $sidebarlist);

        $sidebarlist = array();
        if ($account) {
            $sidebarlist['accountdetail'] = array(
                'tag' => 'View Account Detail',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => 'bankaccounts',
                    'action' => 'view',
                    'id' => $account->id
                )
            );
            $sidebarlist['receivepayment'] = array(
                'tag' => 'Receive Payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'receive_payment',
                    'cb_account_id' => $account->id
                )
            );
            $sidebarlist['makepayment'] = array(
                'tag' => 'Make Payment',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'make_payment',
                    'cb_account_id' => $account->id
                )
            );
            $sidebarlist['movemoney'] = array(
                'tag' => 'Move Money',
                'link' => array(
                    'modules' => $this->_modules,
                    'controller' => $this->name,
                    'action' => 'move_money',
                    'cb_account_id' => $account->id
                )
            );
            $sidebar->addList($account->name . ' Account', $sidebarlist);
        }
    }

    private function payments($function = '')
    {
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

        $sidebar = new SidebarController($this->view);

        $this->sidebar($sidebar, null, $function);

        $this->view->register('sidebar', $sidebar);
        $this->view->set('sidebar', $sidebar);

        $this->view->set('currency', $account->currency);
        $this->view->set('currency_id', $account->currency_id);

        $glaccount = DataObjectFactory::Factory('GLAccount');

        $gl_accounts = $glaccount->nonControlAccounts();

        $this->view->set('gl_accounts', $gl_accounts);

        if (isset($this->_data['glaccount_id'])) {
            $default_glaccount_id = $this->_data['glaccount_id'];
        } else {
            $default_glaccount_id = key($gl_accounts);
        }

        $this->view->set('gl_centres', $this->getCentres($default_glaccount_id));

        $this->view->set('currencies', $this->getAllowedCurrencies($default_account));
        $this->view->set('rate', $this->getCurrencyRate($default_account, $account->currency_id));
    }

    /*
     * Ajax Functions
     */
    public function getOtherAccounts($_id = '')
    {
        // Used by Ajax to return a list of other accounts after selecting the Bank Account
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
        }

        $to_accounts = DataObjectFactory::Factory('CBAccount');
        $cc = new ConstraintChain();

        if (! empty($_id)) {
            $cc->add(new Constraint('id', '!=', $_id));
        }

        $accounts = $to_accounts->getAll($cc);

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $accounts);
            $this->setTemplateName('select_options');
        } else {
            return $accounts;
        }
    }

    public function getAccountCurrencies($_id = '', $_id2 = '')
    {
        // Used by Ajax to return a list of other accounts after selecting the Bank Account
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['id'])) {
                $_id = $this->_data['id'];
            }
            if (! empty($this->_data['id2'])) {
                $_id2 = $this->_data['id2'];
            }
        }

        $account1 = DataObjectFactory::Factory('CBAccount');
        $account1->load($_id);

        $account2 = DataObjectFactory::Factory('CBAccount');
        $account2->load($_id2);

        $currencies = array(
            $account1->currency_id => $account1->currency,
            $account2->currency_id => $account2->currency
        );

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $currencies);
            $this->setTemplateName('select_options');
        } else {
            return $currencies;
        }
    }

    public function getAllowedCurrencies($_cb_account_id = '')
    {
        // Used by Ajax to return a list of other accounts after selecting the Bank Account
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['cb_account_id'])) {
                $_cb_account_id = $this->_data['cb_account_id'];
            }
        }

        $account = DataObjectFactory::Factory('CBAccount');
        $account->load($_cb_account_id);

        $glparams = DataObjectFactory::Factory('GLParams');
        $base_currency_id = $glparams->base_currency();

        $cc = new ConstraintChain();

        if ($account->currency_id != $base_currency_id) {
            $cc->add(new Constraint('id', 'in', '(' . $account->currency_id . ',' . $base_currency_id . ')'));
        }

        $currency = DataObjectFactory::Factory('Currency');

        $currencies = $currency->getAll($cc);

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $account->currency_id);
            $this->view->set('options', $currencies);
            $this->setTemplateName('select_options');
        } else {
            return $currencies;
        }
    }

    public function getCentres($_glaccount_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['glaccount_id'])) {
                $_glaccount_id = $this->_data['glaccount_id'];
            }
        }

        // Used by Ajax to return Centre list after selecting the Account
        $account = DataObjectFactory::Factory('GLAccount');

        $account->load($_glaccount_id);

        $centre_list = $account->getCentres();

        if (isset($this->_data['ajax'])) {
            $this->view->set('options', $centre_list);

            $this->_templateobject->setAdditional('glcentre_id');

            $this->_templateobject->getField('glcentre_id')->setnotnull();

            $this->view->set('model', $this->_templateobject);

            $this->view->set('attribute', 'glcentre_id');

            $this->setTemplateName('select');
        } else {
            return $centre_list;
        }
    }

    public function getAccountRate($_cb_account_id = '', $_to_account_id = '')
    {
        if (isset($this->_data['ajax'])) {
            if (! empty($this->_data['cb_account_id'])) {
                $_cb_account_id = $this->_data['cb_account_id'];
            }
            if (! empty($this->_data['to_account_id'])) {
                $_to_account_id = $this->_data['to_account_id'];
            }
        }

        $rate = '';

        $glparams = DataObjectFactory::Factory('GLParams');

        if (! empty($_cb_account_id) && ! empty($_to_account_id)) {
            $cbaccount = DataObjectFactory::Factory('CBAccount');
            $cbaccount->load($_cb_account_id);

            $toaccount = DataObjectFactory::Factory('CBAccount');
            $toaccount->load($_to_account_id);

            if ($cbaccount->currency_id != $glparams->base_currency() || $toaccount->currency_id != $glparams->base_currency()) {
                $rate = bcadd(round($toaccount->currency_detail->rate / $cbaccount->currency_detail->rate, 2), 0);
            }
        }

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $rate);
            $this->setTemplateName('text_inner');
        } else {
            return $rate;
        }
    }

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

        if (isset($this->_data['ajax'])) {
            $this->view->set('value', $rate);
            $this->setTemplateName('text_inner');
        } else {
            return $rate;
        }
    }

    /*
     * Protected Functions
     */
    protected function getPageName($base = null, $type = null)
    {
        return parent::getPageName((empty($base) ? 'cashbook_transactions' : $base), $type);
    }
}

// End of CbtransactionsController
