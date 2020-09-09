<?php

/**
 * Cashbook Transaction Model
 *
 * @package cashbook
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2018 uzERP LLP (support#uzerp.com). All rights reserved.
 **/
class CBTransaction extends DataObject
{

    protected $version = '$Revision: 1.22 $';

    protected $defaultDisplayFields = array(
        'reference',
        'transaction_date',
        'description',
        'ext_reference',
        'currency_id',
        'gross_value',
        'status',
        'cb_account',
        'company_id',
        'person_id',
        'source',
        'type',
        'payment_type_id',
        'statement_date',
        'statement_page'
    );

    function __construct($tablename = 'cb_transactions')
    {
        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';

        $this->orderby = 'transaction_date';
        $this->orderdir = 'DESC';

        // Define relationships
        $employees = new EmployeeCollection();
        $employees_sh = new SearchHandler(new EmployeeCollection());
        $employee_cc = new ConstraintChain();
        $employee_cc->add(new Constraint('finished_date', 'IS NOT', 'NULL'));
        $employees_sh->addConstraintChain($employee_cc);
        $employees->load($employees_sh);
        $leavers = [];
        foreach ($employees as $employee) {
            $leavers[] = $employee->person_id;
        }

        $person_filter = null;
        if (count($leavers) > 0) {
            $person_filter = new ConstraintChain();
            $person_filter->add(new Constraint('id', 'NOT IN ', '(' . implode(',', $leavers) . ')'));
        }

        $this->belongsTo('CBAccount', 'cb_account_id', 'cb_account');
        $this->belongsTo('Company', 'company_id', 'company');
        $this->belongsTo('Person', 'person_id', 'person', $person_filter, 'surname || \', \' || firstname');
        $this->belongsTo('Currency', 'currency_id', 'currency');
        $this->belongsTo('Currency', 'twin_currency_id', 'twincurrency');
        $this->belongsTo('Currency', 'basecurrency_id', 'basecurrency');
        $this->belongsTo('PaymentType', 'payment_type_id', 'payment_type');
        $this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate');
        $this->hasOne('CBAccount', 'cb_account_id', 'cb_account_detail');

        // Define enumerated types
        $this->setEnum('source', array(
            'C' => 'Cash Book',
            'E' => 'Expenses',
            'G' => 'General Ledger',
            'P' => 'Purchase Ledger',
            'S' => 'Sales Ledger',
            'CV' => 'Cash Book VAT'
        ));

        $this->setEnum('status', array(
            'N' => 'New',
            'R' => 'Reconciled'
        ));

        $this->setEnum('type', array(
            'P' => 'Payment',
            'R' => 'Receipt',
            'RP' => 'Refund Payment',
            'RR' => 'Refund Receipt',
            'T' => 'Transfer'
        ));

        // Define system defaults
        $this->getField('net_value')->setDefault('0.00');

        // Define validation
        $this->getField('gross_value')->addValidator(new ValueValidator('0', '<>'));
    }

    public function person_name()
    {
        if (! $this->person_id) {
            return '';
        }

        $person = DataObjectFactory::Factory('Person');

        $person->load($this->person_id);

        return $person->firstname . ' ' . $person->surname;
    }

    public function allow_refund()
    {
        return ($this->source == 'C' && ($this->type == 'P' || $this->type == 'R'));
    }

    /*
     * Public Static Functions
     */
    public static function saveCashPayment($data, &$errors)
    {
        $db = DB::Instance();
        $db->StartTrans();

        $cb_trans = self::savePayment($data, $errors);

        if (! $cb_trans) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $success = self::saveGLtransaction($cb_trans, $data, $errors);
        if (! $success || count($errors) > 0) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $db->CompleteTrans();
        return $cb_trans;
    }

    public static function moveMoney($data, &$errors)
    {
        $db = DB::Instance();
        $db->StartTrans();

        $from_account = DataObjectFactory::Factory('CBAccount');

        if (isset($data['cb_account_id']) && ! empty($data['cb_account_id'])) {
            $from_account->load($data['cb_account_id']);
        } else {
            $errors[] = 'No bank account (transfer from) supplied';
            return false;
        }

        $to_account = DataObjectFactory::Factory('CBAccount');

        if (isset($data['to_account_id']) && ! empty($data['to_account_id'])) {
            $to_account->load($data['to_account_id']);
        } else {
            $errors[] = 'No bank account (transfer to) supplied';
            return false;
        }

        // Save the original input values
        $data['transaction_net_value'] = $data['net_value'];

        if (empty($data['tax_value'])) {
            $data['tax_value'] = 0.00;
        }

        $data['transaction_tax_value'] = $data['tax_value'];
        $data['transaction_currency_id'] = $data['currency_id'];

        $glparams = DataObjectFactory::Factory('GLParams');

        $base_currency_id = $glparams->base_currency();
        $data['basecurrency_id'] = $base_currency_id;

        $from_values = $data;
        $to_values = $data;

        $from_values['net_value'] = bcmul($from_values['net_value'], - 1);
        $from_values['tax_value'] = bcmul($from_values['tax_value'], - 1);

        $from_values['glaccount_id'] = $to_values['control_glaccount_id'] = $to_account->glaccount_id;
        $from_values['glcentre_id'] = $to_values['control_glcentre_id'] = $to_account->glcentre_id;
        ;
        $to_values['glaccount_id'] = $from_values['control_glaccount_id'] = $from_account->glaccount_id;
        $to_values['glcentre_id'] = $from_values['control_glcentre_id'] = $from_account->glcentre_id;
        ;

        $to_values['cb_account_id'] = $to_values['to_account_id'];

        // Convert value to currency of account that does not match input currency
        if ($data['currency_id'] == $from_account->currency_id) {
            self::convertValues($to_values, 'M');
            $to_values['currency_id'] = $to_account->currency_id;
        } else {
            self::convertValues($from_values, 'D');
            $from_values['currency_id'] = $from_account->currency_id;
        }

        if ($from_account->currency_id != $base_currency_id && $to_account->currency_id != $base_currency_id) {
            // Neither account currency equals base currency
            // so set the rate to the base rate of each of the account currencies
            $from_values['rate'] = $from_account->currency_detail->rate;
            $to_values['rate'] = $to_account->currency_detail->rate;
        }

        // Now get base and twin rates for each of the transactions
        LedgerTransaction::setCurrency($to_values);

        if ($to_account->currency_id != $base_currency_id) {
            // Only need to do this if to_values not in base currency
            LedgerTransaction::setCurrency($from_values);
        } else {
            // to_values in base currency so copy and negate into from_values
            $from_values['gross_value'] = bcadd($from_values['net_value'], $from_values['tax_value']);
            $from_values['base_net_value'] = bcmul($to_values['base_net_value'], - 1);
            $from_values['base_tax_value'] = bcmul($to_values['base_tax_value'], - 1);
            $from_values['base_gross_value'] = bcmul($to_values['base_gross_value'], - 1);
            $from_values['twin_net_value'] = bcmul($to_values['twin_net_value'], - 1);
            $from_values['twin_tax_value'] = bcmul($to_values['twin_tax_value'], - 1);
            $from_values['twin_gross_value'] = bcmul($to_values['twin_gross_value'], - 1);
            $from_values['twin_currency_id'] = $to_values['twin_currency_id'];
            $from_values['twin_rate'] = $to_values['twin_rate'];
        }

        // Now need to ensure the base and twin are equal on both sets of data
        if ($from_values['base_net_value'] != $to_values['base_net_value']) {
            $from_values['base_net_value'] = bcadd(round(bcsub($from_values['base_net_value'], $to_values['base_net_value']) / 2, 2), 0);
            $from_values['base_tax_value'] = bcadd(round(bcsub($from_values['base_tax_value'], $to_values['base_tax_value']) / 2, 2), 0);
            $from_values['base_gross_value'] = bcadd($from_values['base_net_value'], $from_values['base_tax_value']);
            $to_values['base_net_value'] = bcmul($from_values['base_net_value'], - 1);
            $to_values['base_tax_value'] = bcmul($from_values['base_tax_value'], - 1);
            $to_values['base_gross_value'] = bcmul($from_values['base_gross_value'], - 1);
            $from_values['twin_net_value'] = bcadd(round(bcsub($from_values['twin_net_value'], $to_values['twin_net_value']) / 2, 2), 0);
            $from_values['twin_tax_value'] = bcadd(round(bcsub($from_values['twin_tax_value'], $to_values['twin_tax_value']) / 2, 2), 0);
            $from_values['twin_gross_value'] = bcadd($from_values['twin_net_value'], $from_values['base_tax_value']);
            $to_values['twin_net_value'] = bcmul($from_values['twin_net_value'], - 1);
            $to_values['twin_tax_value'] = bcmul($from_values['twin_tax_value'], - 1);
            $to_values['twin_gross_value'] = bcmul($from_values['twin_gross_value'], - 1);
        }

        if (! empty($data['rate'])) {
            $from_values['rate'] = $to_values['rate'] = $data['rate'];
        }

        if (empty($from_values['rate'])) {
            $from_values['rate'] = $to_values['rate'];
        }

        $cb_trans = self::saveTransaction($from_values, $errors);
        if (! $cb_trans) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $cb_trans = self::saveTransaction($to_values, $errors);
        if (! $cb_trans) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        // Set the contra account/centre values
        $to_values['control_reference'] = $from_values['reference'];

        $success = self::saveGLtransaction($cb_trans, $to_values, $errors);
        if (! $success) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        return $db->CompleteTrans();
    }

    public static function saveTransaction(&$data, &$errors)
    {
        if (! isset($data['source'])) {
            $data['source'] = 'C';
        }

        $data['status'] = 'N';

        $db = DB::Instance();
        $db->StartTrans();

        $generator = new CashReferenceNumberHandler();
        $data['reference'] = $generator->handle(DataObjectFactory::Factory('CBTransaction'));

        $trans = DataObject::Factory($data, $errors, 'CBTransaction');

        if ($trans === false || count($errors) > 0) {
            $errors[] = 'Invalid Cash Transaction';
        }

        if (count($errors) == 0 && ! $trans->save()) {
            $errors[] = 'Failed to save Cash Transaction ' . $db->ErrorMsg();
        }

        if (count($errors) == 0 && ! $trans->cb_account_detail->updateBalance($trans)) {
            $errors[] = 'Failed to update ' . $trans->cb_account_detail->name . ' Account Balance' . $db->ErrorMsg();
        }

        if (count($errors) > 0) {
            $db->FailTrans();
            $trans = FALSE;
        }

        $db->CompleteTrans();

        return $trans;
    }

    public static function savePayment(&$data, &$errors)
    {
        $account = DataObjectFactory::Factory('CBAccount');

        if (isset($data['cb_account_id']) && ! empty($data['cb_account_id'])) {
            $account = $account->load($data['cb_account_id']);
            $data['control_glaccount_id'] = $account->glaccount_id;
            $data['control_glcentre_id'] = $account->glcentre_id;
        } else {
            $errors[] = 'No bank account supplied';
            return false;
        }

        $glparams = DataObjectFactory::Factory('GLParams');

        $base_currency_id = $glparams->base_currency();

        // The type of conversion depends on whether the input currency or the account currency equals the base currency
        // - input currency equals base, then multiple value by rate
        // - account currency equals base, then divide value by rate
        // Save the original input values
        $data['transaction_net_value'] = $data['net_value'];

        if (empty($data['tax_value'])) {
            $data['tax_value'] = 0.00;
        }

        $data['transaction_tax_value'] = $data['tax_value'];
        $data['transaction_currency_id'] = $data['currency_id'];

        if ($account->currency_id != $data['currency_id']) {
            if ($data['currency_id'] == $base_currency_id) {
                self::convertValues($data, 'M');
            } else {
                self::convertValues($data, 'D');
            }
        }
        $data['currency_id'] = $account->currency_id;

        if ($account->currency_id == $base_currency_id) {
            $data['rate'] = '';
        }

        if (! empty($data['tax_rate_id']) && empty($data['tax_percentage'])) {
            $taxrate = DataObjectFactory::Factory('TaxRate');

            $taxrate->load($data['tax_rate_id']);

            if ($taxrate->isLoaded()) {
                $data['tax_percentage'] = $taxrate->percentage;
            }
        }

        LedgerTransaction::setCurrency($data);

        return self::saveTransaction($data, $errors);
    }

    public static function saveGLtransaction($cb_trans, $data, &$errors)
    {
        $desc = $cb_trans->description;
        $gl_data = $data;
        $gl_data['comment'] = ! empty($desc) ? $desc : $cb_trans->reference;
        $gl_data['docref'] = $cb_trans->reference;
        $gl_data['reference'] = $cb_trans->ext_reference;
        $gl_data['control_docref'] = $cb_trans->control_reference;

        $glperiod = GLPeriod::getPeriod($cb_trans->transaction_date);

        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            return false;
        }

        $db = DB::Instance();
        $db->StartTrans();

        $gl_data['glperiods_id'] = $glperiod['id'];

        $gl_trans = GLTransaction::makeFromCashbookTransaction($gl_data, $errors);

        if ($gl_trans === false) {
            $errors[] = 'Invalid GL transaction';
        }

        if (count($errors) == 0 && ! GLTransaction::saveTransactions($gl_trans, $errors)) {
            $errors[] = 'Error saving GL transaction';
            $db->FailTrans();
            $gl_trans = FALSE;
        }

        $db->CompleteTrans();

        return $gl_trans;
    }

    /*
     * Private Functions
     */
    private function convertValues(&$data, $type)
    {
        if (empty($data['rate'])) {
            $data['rate'] = 1;
        }
        $data['net_value'] = self::convertCurrency($data['net_value'], $data['rate'], $type);
        $data['tax_value'] = self::convertCurrency($data['tax_value'], $data['rate'], $type);
    }

    private function convertCurrency($value, $rate, $type)
    {
        if ($type == 'D') {
            return bcadd(round(bcdiv($value, $rate, 4), 2), 0);
        } else {
            return bcadd(round(bcmul($value, $rate, 4), 2), 0);
        }
    }
}

// End of CBTransaction
