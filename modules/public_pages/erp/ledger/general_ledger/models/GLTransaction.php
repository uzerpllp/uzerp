<?php

/**
 *	uzERP General Ledger Transaction Model
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
class GLTransaction extends DataObject
{

    protected $version = '$Revision: 1.51 $';

    protected $defaultDisplayFields = array(
        'transaction_date' => 'Date',
        'account' => 'Account',
        'cost_centre' => 'Cost Centre',
        'glperiod' => 'Period',
        'docref' => 'Doc Ref:',
        // ,'value' => 'Value'
        'debit' => 'Debit',
        'credit' => 'Credit',
        'comment' => 'Comment',
        'source' => 'Source',
        'type' => 'Type',
        'glaccount_id',
        'glcentre_id'
    );

    private static $multipliers = array(
        'E' => array(
            'E' => 1,
            'C' => - 1,
            'J' => 1,
            'P' => 1
        ),
        'S' => array(
            'I' => - 1,
            'C' => 1,
            'J' => - 1,
            'R' => 1,
            'RR' => 1,
            'SD' => - 1
        ),
        'P' => array(
            'I' => 1,
            'C' => - 1,
            'J' => 1,
            'P' => 1,
            'RP' => 1,
            'SD' => 1
        ),
        'C' => array(
            'R' => - 1,
            'P' => - 1,
            'RR' => - 1,
            'RP' => - 1,
            'T' => - 1
        ),
        'CV' => array(
            'R' => - 1,
            'P' => - 1
        )
    );

    function __construct($tablename = 'gl_transactions', $source = '', $type = '')
    {
        // Register non-persistent attributes
        $this->setAdditional('credit', 'numeric');
        $this->setAdditional('debit', 'numeric');

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'docref';
        $this->orderby = 'year_period';
        $this->orderdir = 'desc';

        // Define relationships
        $this->belongsTo('GLAccount', 'glaccount_id', 'account');
        $this->belongsTo('GLCentre', 'glcentre_id', 'cost_centre');
        $this->belongsTo('GLPeriod', 'glperiods_id', 'glperiod');
        $this->belongsTo('Currency', 'twin_currency_id', 'twincurrency');

        if (! empty($source) && ! empty($type)) {
            $this->setSoftLinks($source, $type);
        }

        // Define field formats
        $glparams = DataObjectFactory::Factory('GLParams');
        $currency_id = $glparams->base_currency();

        if ($currency_id !== false) {
            $this->getField('value')->setFormatter(new CurrencyFormatter($currency_id));
        }

        $this->getField('credit')->setFormatter(new NumericFormatter());
        $this->getField('debit')->setFormatter(new NumericFormatter());

        // Define validation
        $this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array(
            'glaccount_id' => 'glaccount_id',
            'glcentre_id' => 'glcentre_id'
        )));
        $this->addValidator(new GLPeriodOpenModelValidator($this));

        // Define enumerated types
        $this->setEnum('type', array(
            'A' => 'Accural',
            'C' => 'Credit Note',
            'V' => 'Currency Valuation',
            'CA' => 'Currency Adjustment',
            'D' => 'Depreciation',
            'E' => 'Expense',
            'I' => 'Invoice',
            'J' => 'Journal',
            'N' => 'Net Tax',
            'P' => 'Payment',
            'R' => 'Receipt',
            'RP' => 'Refund Payment',
            'RR' => 'Refund Receipt',
            'SD' => 'Settlement Discount',
            'S' => 'Standard',
            'T' => 'Transfer'
        ));

        $this->setEnum('source', array(
            'A' => 'Asset Register',
            'C' => 'Cash Book',
            'E' => 'Expenses',
            'G' => 'General Ledger',
            'P' => 'Purchase Ledger',
            'S' => 'Sales Ledger',
            'V' => 'VAT Return',
            'CV' => 'Cash book VAT'
        ));
    }

    public function company()
    {
        switch ($this->source) {
            case 'C':
                return $this->{$this->source . $this->type}->company;
            case 'P':
                return $this->{$this->source . $this->type}->supplier;
            case 'S':
                return $this->{$this->source . $this->type}->customer;
        }
        return '';
    }

    public function ext_reference()
    {
        switch ($this->source) {
            case 'C':
            case 'P':
            case 'S':
                return $this->{$this->source . $this->type}->ext_reference;
        }
        return '';
    }

    public static function currencyAdjustment($data, &$errors = array())
    {
        $db = DB::Instance();
        $db->StartTrans();

        $glparams = DataObjectFactory::Factory('GLParams');

        $currency = DataObjectFactory::Factory('Currency');
        $currency->load($glparams->base_currency());

        if (! $currency) {
            $errors[] = 'No currency found';
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        if (empty($data['transaction_date'])) {
            $data['transaction_date'] = date(DATE_FORMAT);
        }

        $glperiod = GLPeriod::getPeriod(fix_date(date(DATE_FORMAT)));

        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $control = self::getControlAccount($data['original_source'], $errors);

        $data['glperiods_id'] = $glperiod['id'];
        $data['source'] = 'G';
        $data['type'] = 'CA';

        $data['glaccount_id'] = $currency->writeoff_glaccount_id;
        $data['glcentre_id'] = $currency->glcentre_id;

        self::setTwinCurrency($data);

        $gl_trans[] = GLTransaction::Factory($data, $errors);

        $data['value'] = $data['value'] * - 1;
        $data['glaccount_id'] = $control['glaccount_id'];
        $data['glcentre_id'] = $control['glcentre_id'];

        self::setTwinCurrency($data);

        $gl_trans[] = GLTransaction::Factory($data, $errors);

        if (count($errors) == 0 && GLTransaction::saveTransactions($gl_trans, $errors) && $db->CompleteTrans()) {
            return true;
        }

        $errors[] = 'Failed to save GL Transaction';

        $db->FailTrans();
        $db->CompleteTrans();

        return false;
    }

    public function load($clause, $override = FALSE, $return = FALSE)
    {
        parent::load($clause, $override, $return);

        if ($this->isLoaded()) {
            $this->setSoftLinks($this->source, $this->type);

            if (! is_null($this->value)) {
                if ($this->value < 0) {
                    $this->credit = bcmul($this->value, - 1);
                } else {
                    $this->debit = bcadd($this->value, 0);
                }
            }
        }

        return $this;
    }

    public function makeFromAsset(Asset $asset, $value, $type, &$errors = array())
    {
        $glperiod = DataObjectFactory::Factory('GLPeriod');

        $glperiod->getCurrentPeriod();

        $gl_data = array();
        $gl_data['glperiods_id'] = $glperiod->id;
        $gl_data['value'] = bcadd($value, 0);

        GLTransaction::setTwinCurrency($gl_data);

        $group = DataObjectFactory::Factory('ARGroup');
        $group->load($asset->argroup_id);

        $location = DataObjectFactory::Factory('ARLocation');
        $location->load($asset->arlocation_id);

        $gl_data['docref'] = 'A' . $asset->id;
        $gl_data['source'] = 'A';

        $glparams = DataObjectFactory::Factory('GLParams');

        switch ($type) {
            case 'disposal':
                $gl_data['type'] = 'J';
                $gl_data['glaccount_id'] = $group->disposals_glaccount_id;
                $gl_data['glcentre_id'] = $location->pl_glcentre_id;
                $gl_data['credit_glaccount_id'] = $group->asset_cost_glaccount_id;
                $gl_data['credit_glcentre_id'] = $location->bal_glcentre_id;
                $gl_data['comment'] = 'Asset Disposal ' . $asset->code;

                break;

            case 'disposal-depreciation':
                $gl_data['type'] = 'J';
                $gl_data['glaccount_id'] = $group->asset_depreciation_glaccount_id;
                $gl_data['glcentre_id'] = $location->bal_glcentre_id;
                $gl_data['credit_glaccount_id'] = $group->disposals_glaccount_id;
                $gl_data['credit_glcentre_id'] = $location->pl_glcentre_id;
                $gl_data['comment'] = 'Asset Disposal ' . $asset->code;

                break;

            case 'depreciation':
                $gl_data['type'] = 'D';
                $gl_data['glaccount_id'] = $group->depreciation_charge_glaccount_id;
                $gl_data['glcentre_id'] = $location->pl_glcentre_id;
                $gl_data['credit_glaccount_id'] = $group->asset_depreciation_glaccount_id;
                $gl_data['credit_glcentre_id'] = $location->bal_glcentre_id;
                $gl_data['comment'] = 'Depreciation charge ' . $asset->code;

                break;
        }

        $gl_transactions = array();

        if (empty($gl_data['glaccount_id']) || empty($gl_data['glcentre_id']) || empty($gl_data['credit_glaccount_id']) || empty($gl_data['credit_glcentre_id'])) {
            $errors[] = 'GL Asset Accounts/Centres not defined';
        } else {
            $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

            $gl_data['twinvalue'] *= - 1;
            $gl_data['value'] *= - 1;
            $gl_data['glaccount_id'] = $gl_data['credit_glaccount_id'];
            $gl_data['glcentre_id'] = $gl_data['credit_glcentre_id'];
            $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);
        }

        return $gl_transactions;
    }

    public function makeFromAssetTransaction(ARTransaction $ar_trans, Asset $asset, &$errors = array())
    {
        $transdate = $ar_trans->transaction_date;

        if (is_numeric($ar_trans->transaction_date)) {
            $transdate = fix_date(date(DATE_FORMAT, $ar_trans->transaction_date));
        }

        $glperiod = GLPeriod::getPeriod($transdate);

        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            return false;
        }

        $gl_data = array();

        $gl_data['glperiods_id'] = $glperiod['id'];
        $gl_data['value'] = $ar_trans->value;

        GLTransaction::setTwinCurrency($gl_data);

        $group = DataObjectFactory::Factory('ARGroup');

        $location = DataObjectFactory::Factory('ARLocation');

        $gl_data['docref'] = 'T' . $ar_trans->id;
        $gl_data['source'] = 'A';
        $gl_data['type'] = 'J';
        $gl_data['comment'] = $asset->code . ' ' . $asset->description;
        $gl_data['transaction_date'] = un_fix_date($ar_trans->transaction_date);

        $glparams = DataObjectFactory::Factory('GLParams');

        switch ($ar_trans->transaction_type) {
            case 'A':
                // Asset Addition
                $group->load($ar_trans->to_group_id);
                $location->load($ar_trans->to_location_id);

                $gl_data['glaccount_id'] = $group->asset_cost_glaccount_id;
                $gl_data['glcentre_id'] = $location->bal_glcentre_id;
                $gl_data['credit_glaccount_id'] = $glparams->ar_pl_suspense_account();
                $gl_data['credit_glcentre_id'] = $glparams->ar_pl_suspense_centre();
                $gl_data['comment'] = 'Asset Addition ' . $gl_data['comment'];

                break;
            case 'D':
                // Asset Disposal
                $group->load($ar_trans->from_group_id);
                $location->load($ar_trans->from_location_id);

                $gl_data['glaccount_id'] = $glparams->ar_disposals_proceeds_account();
                $gl_data['glcentre_id'] = $glparams->ar_disposals_proceeds_centre();
                $gl_data['credit_glaccount_id'] = $group->disposals_glaccount_id;
                $gl_data['credit_glcentre_id'] = $location->pl_glcentre_id;
                $gl_data['comment'] = 'Asset Disposal ' . $gl_data['comment'];

                break;
        }

        $gl_transactions = array();

        if (empty($gl_data['glaccount_id']) || empty($gl_data['glcentre_id']) || empty($gl_data['credit_glaccount_id']) || empty($gl_data['credit_glcentre_id'])) {
            $errors[] = 'GL Asset Accounts/Centres not defined';
        } else {

            $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

            $gl_data['twinvalue'] *= - 1;
            $gl_data['value'] *= - 1;
            $gl_data['glaccount_id'] = $gl_data['credit_glaccount_id'];
            $gl_data['glcentre_id'] = $gl_data['credit_glcentre_id'];

            $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);
        }

        return $gl_transactions;
    }

    public static function makeFromGRN($gl_data, $lines_data, &$errors = array())
    {
        $db = DB::Instance();

        $gl_data['type'] = 'A';
        $gl_data['source'] = 'P';

        $gl_transactions = array();

        foreach ($lines_data as $line) {
            $glperiod = GLPeriod::getPeriod($line['received_date']);

            if ((! $glperiod) || (count($glperiod) == 0)) {
                $errors[] = 'No period exists for this date';
                return array();
            }

            $gl_data['transaction_date'] = un_fix_date($line['received_date']);
            $gl_data['docref'] = $line['order_number'];
            $gl_data['glperiods_id'] = $glperiod['id'];
            $gl_data['glaccount_id'] = $line['glaccount_id'];
            $gl_data['glcentre_id'] = $line['glcentre_id'];
            $gl_data['comment'] = $line['item_description'];

            if ($gl_data['reverse_accrual']) {
                $gl_data['value'] = $line['net_value'] * - 1;
            } else {
                $gl_data['value'] = $line['net_value'];
            }

            if ($line['rate'] != 1) {
                // Convert to base value
                $gl_data['value'] = round(bcmul($line['rate'], $gl_data['value'], 4), 2);
            }

            GLTransaction::setTwinCurrency($gl_data);

            $gl_transaction = GLTransaction::Factory($gl_data, $errors);

            $gl_transactions[] = $gl_transaction;

            $gl_data['twinvalue'] *= - 1;
            $gl_data['value'] *= - 1;
            $gl_data['glaccount_id'] = $gl_data['control_glaccount_id'];
            $gl_data['glcentre_id'] = $gl_data['control_glcentre_id'];

            $gl_transaction = GLTransaction::Factory($gl_data, $errors);

            $gl_transactions[] = $gl_transaction;
        }

        return $gl_transactions;
    }

    public static function makeFromJournalEntry($gl_data, $lines_data, &$errors = array())
    {
        $db = DB::Instance();

        $glperiod = GLPeriod::getPeriod(fix_date($gl_data['transaction_date']));

        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            return array();
        }

        $gl_data['docref'] = $db->GenID('gl_transactions_docref_seq');
        $gl_transactions = array();

        foreach ($lines_data as $line) {
            if ($line['debit'] < 0 || $line['credit'] < 0) {
                $errors[] = 'Credit/Debit values cannot be negative';
            } elseif ($line['debit'] == 0 && $line['credit'] == 0) {
                $errors[] = 'Can\'t enter a journal line without either a credit or a debit';
            } elseif ($line['debit'] > 0 && $line['credit'] > 0) {
                $errors[] = 'A journal line cannot have both a credit and a debit';
            } else {
                $gl_data['value'] = 0;

                if ($line['debit'] > 0) {
                    $gl_data['value'] = BCADD($line['debit'], 0);
                } elseif ($line['credit'] > 0) {
                    $gl_data['value'] = BCMUL($line['credit'], - 1);
                }

                $gl_data['glperiods_id'] = $glperiod['id'];
                $gl_data['glaccount_id'] = $line['glaccount_id'];
                $gl_data['glcentre_id'] = $line['glcentre_id'];
                $gl_data['comment'] = $line['comment'];

                GLTransaction::setTwinCurrency($gl_data);

                $gl_transaction = GLTransaction::Factory($gl_data, $errors);
                $gl_transactions[] = $gl_transaction;

                if (isset($gl_data['accrual'])) {
                    $gl_data['glperiods_id'] = $gl_data['accrual_period_id'];
                    $gl_data['twinvalue'] *= - 1;
                    $gl_data['value'] *= - 1;
                    $gl_data['comment'] = 'Reverse ' . $gl_data['comment'];
                    $gl_transaction = GLTransaction::Factory($gl_data, $errors);

                    $gl_transactions[] = $gl_transaction;
                }
            }
        }

        return $gl_transactions;
    }

    public static function makeFromCashbookTransaction($gl_data, &$errors = array())
    {
        $db = DB::Instance();
        $db->StartTrans();

        $gl_transaction = array();

        $bank_line = GLTransaction::makeCBLine($gl_data, $errors);

        if ($bank_line !== false) {
            $gl_transaction[] = $bank_line;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        if (! empty($gl_data['tax_rate_id'])) {
            $vat_line = GLTransaction::makeCBTax($gl_data, $errors);
            if ($vat_line !== false) {
                $gl_transaction[] = $vat_line;
            } else {
                $db->FailTrans();
                $db->CompleteTrans();
                return false;
            }
        }

        $gl_data['glaccount_id'] = $gl_data['control_glaccount_id'];
        $gl_data['glcentre_id'] = $gl_data['control_glcentre_id'];
        $gl_data['reference'] = $gl_data['control_reference'];

        $dist_line = GLTransaction::makeCBControl($gl_data, $errors);

        if ($dist_line !== false) {
            $gl_transaction[] = $dist_line;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $db->CompleteTrans();

        return $gl_transaction;
    }

    public static function makeFromLedgerJournal($trans, $data, &$errors = array())
    {
        $db = DB::Instance();
        $db->StartTrans();
        $gl_data = array();
        $gl_transactions = array();

        $gl_data = $data;
        $gl_data['docref'] = $trans->our_reference;
        $gl_data['transaction_date'] = un_fix_date($trans->transaction_date);
        $gl_data['type'] = $data['transaction_type'];
        $gl_data['reference'] = ! empty($desc) ? $desc : $trans->ext_reference;

        $glperiod = GLPeriod::getPeriod($trans->transaction_date);

        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $gl_data['glperiods_id'] = $glperiod['id'];

        $trans_line = GLTransaction::makeCBLine($gl_data, $errors);

        if ($trans_line !== false) {
            $gl_transactions[] = $trans_line;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        if (count($errors) > 0) {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $control = self::getControlAccount($gl_data['source'], $errors);

        foreach ($control as $key => $value) {
            $gl_data[$key] = $value;
        }
        // $control_line = GLTransaction::makeLedgerControl($gl_data, $errors);
        $control_line = GLTransaction::makeCBControl($gl_data, $errors);

        if ($control_line !== false) {
            $gl_transactions[] = $control_line;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }

        $db->CompleteTrans();

        return $gl_transactions;
    }

    public static function makeFromExpenseTransaction(LedgerTransaction $transaction, Expense $source, &$errors = array())
    {
        $newerrors = array();

        $db = DB::Instance();
        $db->StartTrans();

        // sort out the header details
        $gl_transactions = array();
        $gl_data = array();

        // the gl docref is the invoice number
        $gl_data['docref'] = $source->expense_number;
        $gl_data['reference'] = $source->our_reference;

        // dates should be the same
        $gl_data['transaction_date'] = un_fix_date($transaction->transaction_date);

        // first character of class identifies source
        // TODO: this should be returned from array based on class of $transaction
        $gl_data['source'] = substr(strtoupper(get_class($transaction)), 0, 1);

        // type depends on Invoice or Credit Note
        $gl_data['type'] = $transaction->transaction_type;

        // the description is one from a number of bits of information
        // (description is compulsory for GL, but the options aren't for SLTransaction and SInvoice)
        $desc = $source->description;
        $ext_ref = $source->ext_reference;

        if (! empty($desc)) {
            $header_desc = $desc;
        } elseif (! empty($ext_ref)) {
            $header_desc = $ext_ref;
        } else {
            $header_desc = $source->expense_number;
        }

        $gl_data['comment'] = $header_desc;

        // set the period based on invoice date
        $glperiod = GLPeriod::getPeriod($source->expense_date);
        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }
        $gl_data['glperiods_id'] = $glperiod['id'];

        $gl_data['twin_currency_id'] = $source->twin_currency_id;
        $gl_data['twin_rate'] = $source->twin_rate;

        // there needs to be a tax element
        $gl_data['base_tax_value'] = $source->base_tax_value;
        $gl_data['twin_tax_value'] = $source->twin_tax_value;
        $vat_element = GLTransaction::makeCBTax($gl_data, $newerrors);

        if ($vat_element !== false) {
            $gl_transactions[] = $vat_element;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            $errors += $newerrors;
            return false;
        }

        // EU acquisition?
        $eu_acquisition = false;
        if ($gl_data['source'] == 'P') {
            $tax_status = DataObjectFactory::Factory('TaxStatus');
            if ($tax_status->load($source->tax_status_id)) {
                $eu_acquisition = ($tax_status->eu_tax == 't');
            }
        }

        $eu_gl_data = $gl_data;
        $eu_gl_data['value'] = 0;
        $eu_gl_data['twinvalue'] = 0;

        // this is the control element (used to balance the tax and lines)
        $gl_data['base_gross_value'] = $source->base_gross_value;
        $gl_data['twin_gross_value'] = $source->twin_gross_value;

        $control = self::getControlAccount($gl_data['source'], $errors);

        foreach ($control as $key => $value) {
            $gl_data[$key] = $value;
        }

        $control = GLTransaction::makeCBControl($gl_data, $newerrors);

        if ($control !== false) {
            $gl_transactions[] = $control;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            $errors += $newerrors;
            return false;
        }

        // then do the invoice lines
        $lines = $source->lines;
        foreach ($lines as $line) {
            // provide some alternatives to get a comment
            $i_desc = $line->item_description;

            $desc = (! empty($i_desc) ? $i_desc : $line->description);
            $desc = (! empty($desc) ? $desc : '');

            $gl_data['comment'] = $desc;
            $gl_data['glaccount_id'] = $line->glaccount_id;
            $gl_data['glcentre_id'] = $line->glcentre_id;
            $gl_data['base_net_value'] = $line->base_net_value;
            $gl_data['twin_net_value'] = $line->twin_net_value;

            // Calculate tax value if EU acquisition
            if (($eu_acquisition) && ($line->tax_rate_id)) {
                $tax_rate = DataObjectFactory::Factory('TaxRate');
                if (($tax_rate->load($line->tax_rate_id) && ($tax_rate->percentage > 0))) {
                    $tax_rate_mult = (1 + ($tax_rate->percentage / 100));
                    $eu_gl_data['value'] += ($line->base_net_value * $tax_rate_mult) - $line->base_net_value;
                    $eu_gl_data['twinvalue'] += ($line->twin_net_value * $tax_rate_mult) - $line->twin_net_value;
                }
            }

            $element = GLTransaction::makeCBLine($gl_data, $newerrors);
            if ($element !== false) {
                $gl_transactions[] = $element;
            } else {
                $db->FailTrans();
                $db->CompleteTrans();
                $errors += $newerrors;
                return false;
            }
        }

        if ($eu_acquisition) {
            $eu_tax_elements = GLTransaction::makeEuTax($eu_gl_data, $newerrors);
            foreach ($eu_tax_elements as $eu_tax_element) {
                if ($eu_tax_element === false) {
                    $db->FailTrans();
                    $db->CompleteTrans();
                    $errors += $newerrors;
                    return false;
                }
                $gl_transactions[] = $eu_tax_element;
            }
        }

        $db->CompleteTrans();

        return $gl_transactions;
    }

    public static function makeFromLedgerTransaction(LedgerTransaction $transaction, Invoice $invoice, &$errors = array())
    {
        $newerrors = array();

        $db = DB::Instance();
        $db->StartTrans();

        // sort out the header details
        $gl_transactions = array();
        $gl_data = array();

        // the gl docref is the invoice number
        $gl_data['docref'] = $invoice->invoice_number;
        $gl_data['reference'] = $invoice->our_reference;

        // dates should be the same
        $gl_data['transaction_date'] = un_fix_date($invoice->invoice_date);

        // first character of class identifies source
        $gl_data['source'] = substr(strtoupper(get_class($transaction)), 0, 1);

        // type depends on Invoice or Credit Note
        $gl_data['type'] = $invoice->transaction_type;

        // the description is one from a number of bits of information
        // (description is compulsory for GL, but the options aren't for SLTransaction and SInvoice)
        $desc = $invoice->description;
        $ext_ref = $invoice->ext_reference;
        $sales_order_id = $invoice->sales_order_id;

        if (! empty($desc)) {
            $header_desc = $desc;
        } elseif (! empty($ext_ref)) {
            $header_desc = $ext_ref;
        } elseif (! empty($sales_order_id)) {
            $header_desc = $sales_order_id;
        } else {
            $header_desc = $invoice->invoice_number;
        }

        $gl_data['comment'] = $header_desc;

        // another docref
        $gl_data['docref2'] = $invoice->sales_order_id;

        // set the period based on invoice date
        $glperiod = GLPeriod::getPeriod($invoice->invoice_date);
        if ((! $glperiod) || (count($glperiod) == 0)) {
            $errors[] = 'No period exists for this date';
            $db->FailTrans();
            $db->CompleteTrans();
            return false;
        }
        $gl_data['glperiods_id'] = $glperiod['id'];

        $gl_data['twin_currency_id'] = $invoice->twin_currency_id;
        $gl_data['twin_rate'] = $invoice->twin_rate;

        // there needs to be a tax element
        $gl_data['base_tax_value'] = $invoice->base_tax_value;
        $gl_data['twin_tax_value'] = $invoice->twin_tax_value;
        $vat_element = GLTransaction::makeCBTax($gl_data, $newerrors);

        if ($vat_element !== false) {
            $gl_transactions[] = $vat_element;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            $errors += $newerrors;
            return false;
        }

        // EU acquisition?
        $eu_acquisition = false;
        if ($gl_data['source'] == 'P') {
            $tax_status = DataObjectFactory::Factory('TaxStatus');
            if ($tax_status->load($invoice->tax_status_id)) {
                $eu_acquisition = ($tax_status->eu_tax == 't');
            }
        }

        $eu_gl_data = $gl_data;
        $eu_gl_data['value'] = 0;
        $eu_gl_data['twinvalue'] = 0;

        // this is the control element (used to balance the tax and lines)
        $gl_data['base_gross_value'] = $invoice->base_gross_value;
        $gl_data['twin_gross_value'] = $invoice->twin_gross_value;

        $control = self::getControlAccount($gl_data['source'], $errors);

        foreach ($control as $key => $value) {
            $gl_data[$key] = $value;
        }

        $control = GLTransaction::makeCBControl($gl_data, $newerrors);

        if ($control !== false) {
            $gl_transactions[] = $control;
        } else {
            $db->FailTrans();
            $db->CompleteTrans();
            $errors += $newerrors;
            return false;
        }

        // then do the invoice lines
        $lines = $invoice->lines;
        foreach ($lines as $line) {
            // provide some alternatives to get a comment
            $i_desc = $line->item_description;

            $desc = (! empty($i_desc) ? $i_desc : $line->description);
            $desc = (! empty($desc) ? $desc : '');

            $gl_data['comment'] = $desc;
            $gl_data['glaccount_id'] = $line->glaccount_id;
            $gl_data['glcentre_id'] = $line->glcentre_id;
            $gl_data['base_net_value'] = $line->base_net_value;
            $gl_data['twin_net_value'] = $line->twin_net_value;

            // Calculate tax value if EU acquisition
            if (($eu_acquisition) && ($line->tax_rate_id)) {
                $tax_rate = DataObjectFactory::Factory('TaxRate');
                if (($tax_rate->load($line->tax_rate_id) && ($tax_rate->percentage > 0))) {
                    $tax_rate_mult = (1 + ($tax_rate->percentage / 100));
                    $eu_gl_data['value'] += ($line->base_net_value * $tax_rate_mult) - $line->base_net_value;
                    $eu_gl_data['twinvalue'] += ($line->twin_net_value * $tax_rate_mult) - $line->twin_net_value;
                }
            }

            $element = GLTransaction::makeCBLine($gl_data, $newerrors);
            if ($element !== false) {
                $gl_transactions[] = $element;
            } else {
                $db->FailTrans();
                $db->CompleteTrans();
                $errors += $newerrors;
                return false;
            }
        }

        if ($eu_acquisition) {
            $eu_tax_elements = GLTransaction::makeEuTax($eu_gl_data, $newerrors);
            foreach ($eu_tax_elements as $eu_tax_element) {
                if ($eu_tax_element === false) {
                    $db->FailTrans();
                    $db->CompleteTrans();
                    $errors += $newerrors;
                    return false;
                }
                $gl_transactions[] = $eu_tax_element;
            }
        }

        $db->CompleteTrans();

        return $gl_transactions;
    }

    public static function makeFromVATJournalEntry($gl_data, &$errors = array())
    {
        $db = DB::Instance();
        $net = $gl_data['value']['net'];
        $vat = $gl_data['value']['vat'];
        $gl_transactions = array();

        // Write Net Value
        $gl_data['docref'] = $db->GenID('gl_transactions_docref_seq');
        $gl_data['value'] = $net;
        GLTransaction::setTwinCurrency($gl_data);
        $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

        // Write Vat Value
        $gl_data['value'] = $vat;
        GLTransaction::setTwinCurrency($gl_data);
        $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

        // Write Net contra entry
        $gl_data['docref'] = $db->GenID('gl_transactions_docref_seq');
        $gl_data['value'] = bcmul($net, - 1);
        GLTransaction::setTwinCurrency($gl_data);
        $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

        // Write Vat control entry
        $gl_data['value'] = bcmul($vat, - 1);
        $gl_data['glaccount_id'] = $gl_data['vat_account'];
        $glparams = DataObjectFactory::Factory('GLParams');
        $gl_data['glcentre_id'] = $glparams->balance_sheet_cost_centre();
        GLTransaction::setTwinCurrency($gl_data);
        $gl_transactions[] = GLTransaction::Factory($gl_data, $errors);

        return $gl_transactions;
    }

    public static function makeEuTax($transaction, &$errors = array())
    {
        $mult = self::$multipliers[$transaction['source']][$transaction['type']];
        $eu_tax_element = $transaction;

        $glparams = DataObjectFactory::Factory('GLParams');

        $eu_tax_element['glaccount_id'] = $glparams->eu_acquisitions();

        if ($eu_tax_element['glaccount_id'] === false) {
            $errors[] = 'EU Acquisitions Account Code not found';
        }

        $eu_tax_element['glcentre_id'] = $glparams->balance_sheet_cost_centre();

        if ($eu_tax_element['glcentre_id'] === false) {
            $errors[] = 'Balance Sheet Cost Centre Code not found';
        }

        $eu_tax_element['value'] = bcmul($mult, $eu_tax_element['value']);
        $eu_tax_element['twinvalue'] = bcmul($mult, $eu_tax_element['twinvalue']);

        $eu_tax_elements = array();
        $eu_tax_elements[] = GLTransaction::Factory($eu_tax_element, $errors);

        $eu_tax_element['value'] *= - 1;
        $eu_tax_element['twinvalue'] *= - 1;
        $eu_tax_elements[] = GLTransaction::Factory($eu_tax_element, $errors);

        return $eu_tax_elements;
    }

    public static function makeCBLine($transaction, &$errors = array())
    {
        $mult = self::$multipliers[$transaction['source']][$transaction['type']];

        $transaction['value'] = $mult * $transaction['base_net_value'];
        $transaction['twinvalue'] = $mult * $transaction['twin_net_value'];

        return GLTransaction::Factory($transaction, $errors);
    }

    public static function makeCBTax($transaction, &$errors = array())
    {
        $mult = self::$multipliers[$transaction['source']][$transaction['type']];

        $element = array();
        $element = $transaction;

        $gl_params = DataObjectFactory::Factory('GLParams');

        $element['glaccount_id'] = false;

        switch ($element['source']) {
            case ('S'):
                $element['glaccount_id'] = $gl_params->vat_output();
                break;
            case ('E'):
            case ('P'):
                $element['glaccount_id'] = $gl_params->vat_input();
                break;
            case ('C'):
                $element['glaccount_id'] = ($transaction['type'] == 'R') ? $gl_params->vat_output() : $gl_params->vat_input();
                break;
        }

        if ($element['glaccount_id'] === false) {
            $errors[] = 'VAT Output Account Code not found';
        }

        $element['glcentre_id'] = $gl_params->balance_sheet_cost_centre();

        if ($element['glcentre_id'] === false) {
            $errors[] = 'Balance Sheet Cost Centre Code not found';
        }

        $element['value'] = $mult * $transaction['base_tax_value'];
        $element['twinvalue'] = $mult * $transaction['twin_tax_value'];

        return GLTransaction::Factory($element, $errors);
    }

    public static function makeCBControl($transaction, &$errors = array())
    {
        $mult = self::$multipliers[$transaction['source']][$transaction['type']];

        $transaction['value'] = ($mult * - 1) * $transaction['base_gross_value'];
        $transaction['twinvalue'] = ($mult * - 1) * $transaction['twin_gross_value'];

        return GLTransaction::Factory($transaction, $errors);
    }

    /**
     * Save the Transaction and the lines
     * performs a check that the lines sum to zero
     */
    public static function saveTransactions($gl_transactions, &$errors = array())
    {
        $db = DB::Instance();
        $db->StartTrans();

        $result = true;

        if (! is_array($gl_transactions) || empty($gl_transactions)) {
            $errors[] = 'No transactions to save';
        } else {

            $total = array();

            foreach ($gl_transactions as $transline) {
                set_time_limit(5);

                $result = $transline->save();

                if ($result === false) {
                    $errors[] = 'Error saving GL Transaction : ' . $db->ErrorMsg();
                    $db->FailTrans();
                    return $db->CompleteTrans();
                } elseif (! $transline->updateBalance($errors)) {
                    $db->FailTrans();
                    return $db->CompleteTrans();
                }

                if (isset($total[$transline->glperiods_id])) {
                    $total[$transline->glperiods_id] = bcadd($total[$transline->glperiods_id], $transline->value);
                } else {
                    $total[$transline->glperiods_id] = $transline->value;
                }
            }

            foreach ($total as $period_total) {
                if ($period_total != 0) {
                    $errors[] = 'Transaction total must equal zero. It equals: ' . $period_total;
                    $db->FailTrans();
                    break;
                }
            }
        }

        if (count($errors) > 0) {
            $errors[] = 'Failed to save GL Transaction';
            $db->FailTrans();
        }

        set_time_limit(10);

        return $db->CompleteTrans();
    }

    public function save()
    {
        $db = DB::Instance();
        $db->StartTrans();

        $result = parent::save();

        if ($result === false) {
            $db->FailTrans();
        }

        $db->CompleteTrans();

        return $result;
    }

    public function updateBalance(&$errors = array())
    {
        $db = DB::Instance();

        $db->StartTrans();

        $glbalance = DataObjectFactory::Factory('GLBalance');

        $cc = new ConstraintChain(); // then we start a chain

        $cc->add(new Constraint('glaccount_id', '=', $this->glaccount_id));
        $cc->add(new Constraint('glcentre_id', '=', $this->glcentre_id));
        $cc->add(new Constraint('glperiods_id', '=', $this->glperiods_id));

        $glbalance = $glbalance->loadby($cc);

        $data = array();

        $data['glaccount_id'] = $this->glaccount_id;
        $data['glcentre_id'] = $this->glcentre_id;
        $data['glperiods_id'] = $this->glperiods_id;
        $data['value'] = $this->value;
        $data['lastupdated']    = $glbalance->lastupdated;  //concurrency control

        if ($glbalance !== false) {
            $data['id'] = $glbalance->id;
            $data['value'] = bcadd($data['value'], $glbalance->value);
        }

        $newerrors = array();
        $glbalance = GLBalance::Factory($data, $newerrors, 'GLBalance');

        if (count($newerrors) > 0) {
            $errors += $newerrors;
            $db->FailTrans();
        } elseif ($glbalance !== false) {
            $result = $glbalance->save();

            if ($result === false) {
                $errors[] = 'Error updating GL Balance : ' . $db->ErrorMsg();
                $db->FailTrans();
            }
        }

        return $db->CompleteTrans();
    }

    /**
     * Takes an array by reference, and sets 'twincurrency_id' and 'twinrate' to the appropriate values
     */
    public function setTwinCurrency(&$data)
    {
        $glparams = DataObjectFactory::Factory('GLParams');

        $twin_currency = DataObjectFactory::Factory('Currency');
        $twin_currency->load($glparams->twin_currency());

        $data['twin_currency_id'] = $twin_currency->id;
        $data['twin_rate'] = $twin_currency->rate;
        $data['twinvalue'] = round(bcmul($twin_currency->rate, $data['value'], 4), 2);
    }

    public static function Factory($data, &$errors = array())
    {
        if (empty($data['source']) || empty($data['type'])) {
            $errors[] = 'GL Transaction : Missing source/type';
            return false;
        }

        $gltransaction = new GLTransaction('gl_transactions', $data['source'], $data['type']);

        return parent::Factory($data, $errors, $gltransaction);
    }

    /*
     * Private Functions
     */
    private function getControlAccount($source, &$errors)
    {
        $gl_params = DataObjectFactory::Factory('GLParams');

        // there's a dummy GLAccount
        $control = array();

        switch ($source) {
            case 'E':
                $control['glaccount_id'] = $gl_params->expenses_control_account();
                break;
            case 'P':
                $control['glaccount_id'] = $gl_params->purchase_ledger_control_account();
                break;
            case 'S':
                $control['glaccount_id'] = $gl_params->sales_ledger_control_account();
                break;
            default:
                $control['glaccount_id'] = false;
        }

        if ($control['glaccount_id'] === false) {
            $errors[] = 'Ledger Control Account Code not found';
        }

        // and a same dummy cost centre
        $control['glcentre_id'] = $gl_params->balance_sheet_cost_centre();

        if ($control['glcentre_id'] === false) {
            $errors[] = 'Balance Sheet Cost Centre Code not found';
        }

        return $control;
    }

    private function getTaxStatus()
    {
        $tax_status = null;

        switch ($this->source) {
            case 'S':
                $sinvoice = DataObjectFactory::Factory('SInvoice');
                if (! $sinvoice->loadBy('invoice_number', $this->docref)) {
                    break;
                }
                $tax_status = DataObjectFactory::Factory('TaxStatus');
                if (! $tax_status->load($sinvoice->tax_status_id)) {
                    $tax_status = null;
                }
                break;
            case 'P':
                $pinvoice = DataObjectFactory::Factory('PInvoice');
                if (! $pinvoice->loadBy('invoice_number', $this->docref)) {
                    break;
                }
                $tax_status = DataObjectFactory::Factory('TaxStatus');
                if (! $tax_status->load($pinvoice->tax_status_id)) {
                    $tax_status = null;
                }
                break;
        }

        return $tax_status;
    }

    private function setSoftLinks($source, $type)
    {

        // The name for these links is determined from the source and type on the GL Transaction
        switch (strtoupper($source . $type)) {
            case 'PI':
                $this->hasOne('PInvoice', 'docref', 'pi', 'invoice_number');
                break;
            case 'PC':
                $this->hasOne('PInvoice', 'docref', 'pc', 'invoice_number');
                break;
            case 'SI':
                $this->hasOne('SInvoice', 'docref', 'si', 'invoice_number');
                break;
            case 'SC':
                $this->hasOne('SInvoice', 'docref', 'sc', 'invoice_number');
                break;
            case 'CP':
                $this->hasOne('CBTransaction', 'docref', 'cp', 'reference');
                break;
            case 'CR':
                $this->hasOne('CBTransaction', 'docref', 'cr', 'reference');
                break;
            case 'CT':
                $this->hasOne('CBTransaction', 'docref', 'ct', 'reference');
                break;
            case 'PJ':
                $this->hasOne('PLTransaction', 'docref', 'pj', 'our_reference');
                break;
            case 'PA':
                $this->hasOne('POrder', 'docref', 'pa', 'order_number');
                break;
            case 'PP':
                $this->hasOne('PLTransaction', 'docref', 'pp', 'our_reference');
                break;
            case 'SJ':
                $this->hasOne('SLTransaction', 'docref', 'sj', 'our_reference');
                break;
            case 'SP':
                $this->hasOne('SLTransaction', 'docref', 'sp', 'our_reference');
                break;
        }
    }
}

// End of GLTransaction
