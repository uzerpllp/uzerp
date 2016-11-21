<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class PeriodicPayment extends DataObject
{

    protected $version = '$Revision: 1.13 $';

    private $adder = array();

    protected $defaultDisplayFields = array(
        'status',
        'source',
        'company',
        'person',
        'cb_account' => 'Bank Account',
        'currency',
        'description',
        'ext_reference',
        'payment_type',
        'frequency',
        'next_due_date',
        'variable',
        'net_value',
        'tax_value',
        'gross_value'
    );

    function __construct($tablename = 'periodic_payments')
    {

        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        // $this->identifierField='';
        // $this->orderby='';

        // Define relationships
        $this->belongsTo('Company', 'company_id', 'company');
        $this->belongsTo('Person', 'person_id', 'person');
        $this->belongsTo('Currency', 'currency_id', 'currency');
        $this->belongsTo('CBAccount', 'cb_account_id', 'cb_account');
        $this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate');
        $this->belongsTo('PaymentType', 'payment_type_id', 'payment_type');
        $this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
        $this->belongsTo('GLCentre', 'glcentre_id', 'glcentre');

        // Define enumerated types
        $this->setEnum('status', array(
            'A' => 'Active',
            'S' => 'Suspended'
        ));

        $this->setEnum('source', array(
            'CR' => 'Cashbook Receipt',
            'CP' => 'Cashbook Payment',
            'SR' => 'Sales Ledger Receipt',
            'PP' => 'Purchase Ledger Payment'
        ));

        $this->setEnum('frequency', array(
            'W' => 'Weekly',
            'M' => 'Monthly',
            'Q' => 'Quarterly',
            'Y' => 'Yearly'
        ));

        // Set the incremental details for the frequencies
        $this->adder = array(
            'W' => array(
                'type' => 'days',
                'multiplier' => 7
            ),
            'M' => array(
                'type' => 'months',
                'multiplier' => 1
            ),
            'Q' => array(
                'type' => 'months',
                'multiplier' => 3
            ),
            'Y' => array(
                'type' => 'months',
                'multiplier' => 12
            )
        );

        // Define system defaults
        $this->getField('net_value')->setDefault('0.00');
        $this->getField('tax_value')->setDefault('0.00');
        $this->getField('gross_value')->setDefault('0.00');

        // Define validation
    }

    function cb_loaded()
    {

        // define field formats
        $this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
    }

    public function getLedgerData(&$data)
    {
        if ($this->source == 'SR') {
            $company = DataObjectFactory::Factory('SLCustomer');
            $id = 'slmaster_id';
        } elseif ($this->source == 'PP') {
            $company = DataObjectFactory::Factory('PLSupplier');
            $id = 'plmaster_id';
        } else {
            return;
        }

        $company->loadBy('company_id', $this->company_id);

        if ($company->isLoaded()) {
            $data[$id] = $company->{$company->idField};
            $data['payment_term_id'] = $company->payment_term_id;
        }

        return;
    }

    function makePaymentTransaction()
    {
        $data = array();

        $this->getLedgerData($data);

        $data['glaccount_id'] = $this->glaccount_id;
        $data['glcentre_id'] = $this->glcentre_id;
        $data['cb_account_id'] = $this->cb_account_id;
        $data['currency_id'] = $this->currency_id;
        $data['tax_rate_id'] = $this->tax_rate_id;
        $data['company_id'] = $this->company_id;
        $data['person_id'] = $this->person_id;
        $data['payment_type_id'] = $this->payment_type_id;
        $data['transaction_date'] = un_fix_date($this->next_due_date);
        $data['source'] = substr($this->source, 0, 1);
        $data['transaction_type'] = $data['type'] = substr($this->source, 1, 1);

        return $data;
    }

    function nextDate()
    {
        $increment = '+' . $this->adder[$this->frequency]['multiplier'] . ' ' . $this->adder[$this->frequency]['type'];

        if ($this->frequency == 'M') {

            // Need to check date when adding months;
            // e.g. adding 1 month to 30/01/2008 returns 01/03/2008
            // should return last day of next month i.e. 29/02/2008

            // echo 'Start Date='.$this->start_date.'<br>';
            // echo 'Next Due Date='.$this->next_due_date.'<br>';
            // echo 'Increment='.$increment.'<br>';

            $startday = date('d', strtotime($this->start_date));
            $currentday = date('d', strtotime($this->next_due_date));
            $currentmonth = date('m', strtotime($this->next_due_date));
            $nextdate = date(DATE_FORMAT, strtotime($increment, strtotime($this->next_due_date)));

            if ($startday != $currentday) {

                // the current day differs from the original start day
                // so add the difference to the next date
                // if this takes the date into the following month
                // it will be adjusted below to the last day of the month

                $increment = '+' . ($startday - $currentday) . ' days';

                // echo 'increment='.$increment.'<br>';

                $nextdate = date(DATE_FORMAT, strtotime($increment, strtotime(fix_date($nextdate))));

                // echo 'New next date='.$nextdate.'<br>';
            }

            $nextmonth = date('m', strtotime(fix_date($nextdate)));

            // echo 'Current Month='.$currentmonth.' Next Month='.$nextmonth.'<br>';

            if (($currentmonth == 12 ? 0 : $currentmonth) + $this->adder[$this->frequency]['multiplier'] != $nextmonth) {

                // skipped too many months because current day does not exist in required next month
                // so set the date to be last day of required next month
                $nextdate = date(DATE_FORMAT, strtotime('-1 days', strtotime(date('Y-m-01', strtotime(fix_date($nextdate))))));
            }
        } else {
            $nextdate = date(DATE_FORMAT, strtotime($increment, strtotime($this->next_due_date)));
        }

        $this->next_due_date = fix_date($nextdate);
    }

    public static function Factory($data, &$errors = array(), $do_name = null)
    {
        $model = DataObjectFactory::Factory($do_name);

        if (! empty($data['source']) && ($data['source'] == 'CR' || $data['source'] == 'CP')) {
            $model->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array(
                'glaccount_id' => 'glaccount_id',
                'glcentre_id' => 'glcentre_id'
            )));
        }

        return parent::Factory($data, $errors, $model);
    }
}

// end of PeriodicPayment
