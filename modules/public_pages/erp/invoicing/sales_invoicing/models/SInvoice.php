<?php

/**
 *	uzERP Sales Invoice Model
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
class SInvoice extends Invoice
{

    protected $version = '$Revision: 1.60 $';

    protected $defaultDisplayFields = array(
        'invoice_number',
        'customer',
        'person',
        'sales_order_number',
        'invoice_date',
        'ext_reference',
        'transaction_type',
        'status',
        'gross_value',
        'currency',
        'base_gross_value',
        'date_printed',
        'print_count',
        'slmaster_id',
        'sales_order_id',
        'project_id'
    );

    function __construct($tablename = 'si_header')
    {

        // Register non-persistent attributes

        // Construct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'invoice_number';
        $this->view = '';

        // Set ordering attributes
        $this->orderby = array(
            'invoice_date',
            'invoice_number'
        );
        $this->orderdir = array(
            'DESC',
            'DESC'
        );

        $this->validateUniquenessOf('invoice_number');

        // Define relationships
        $this->hasOne('SLCustomer', 'slmaster_id', 'customerdetail');
        $this->hasOne('Address', 'del_address_id', 'del_address');
        $this->hasOne('Address', 'inv_address_id', 'inv_address');
        $this->hasOne('Person', 'person_id', 'persondetail');
        $this->hasOne('SOrder', 'sales_order_id', 'order');
        $this->hasOne('SystemCompany', 'usercompanyid', 'system_company');
        $this->hasOne('PaymentTerm', 'payment_term_id', 'payment_term');
        $this->hasOne('WHAction', 'despatch_action', 'despatch_from');

        $this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
        $this->belongsTo('SOrder', 'sales_order_id', 'sales_order_number');
        $this->belongsTo('Currency', 'currency_id', 'currency');
        $this->belongsTo('Currency', 'twin_currency_id', 'twin');
        $this->belongsTo('PaymentTerm', 'payment_term_id', 'payment_terms');
        $this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status');
        $this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
        $this->belongsTo('Project', 'project_id', 'project');
        $this->belongsTo('Task', 'task_id', 'task');
        $this->belongsTo('DeliveryTerm', 'delivery_term_id', 'delivery_term');
        $this->belongsTo('WHAction', 'despatch_action');

        $this->setComposite('Address', 'inv_address_id', 'invoice_address', array(
            'street1',
            'street2',
            'street3',
            'town',
            'county',
            'postcode',
            'countrycode'
        ));

        $this->hasMany('SInvoiceLine', 'lines', 'invoice_id');
        $this->hasMany('STTransaction', 'transactions', 'process_id');

        // Define enumerated types
        $this->setEnum('transaction_type', array(
            'I' => 'Invoice',
            'C' => 'Credit Note',
            'T' => 'Template'
        ));

        $this->setEnum('status', array(
            'N' => 'New',
            'O' => 'Open',
            'Q' => 'Query',
            'P' => 'Paid'
        ));

        // Define field formats
        $params = DataObjectFactory::Factory('GLParams');
        $base_currency = $params->base_currency();

        $this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
        $this->getField('base_tax_value')->setFormatter(new CurrencyFormatter($base_currency));
        $this->getField('base_gross_value')->setFormatter(new CurrencyFormatter($base_currency));

        // Define system defaults
        $this->getField('transaction_type')->setDefault('I');

        // Do not allow links to the following
        $this->linkRules = array(
            'lines' => array(
                'actions' => array(),
                'rules' => array()
            ),
            'transactions' => array(
                'tag' => 'show_ST_transactions',
                'newtab' => array(
                    'new' => true
                ),
                'actions' => array(
                    'link'
                ),
                'rules' => array()
            )
        );
    }

    function cb_loaded()
    {

        // then set these formatters here because they depend on the loaded currency_id
        $this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('settlement_discount')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
        $this->getField('twin_net_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
        $this->getField('twin_tax_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
        $this->getField('twin_gross_value')->setFormatter(new CurrencyFormatter($this->_data['twin_currency_id']));
    }

    public static function Factory($header_data, &$errors = [], $do_name = null)
    {
        $customer = DataObjectFactory::Factory('SLCustomer');
        $customer = $customer->load($header_data['slmaster_id']);

        if ($customer) {
            $header_data['currency_id'] = $customer->currency_id;
            $header_data['payment_term_id'] = $customer->payment_term_id;
            $header_data['tax_status_id'] = $customer->tax_status_id;
        }

        if (empty($header_data['despatch_action']))
        {
            $header_data['despatch_action'] = $customer->despatch_action;
        }

        $header = Invoice::makeHeader($header_data, 'SInvoice', $errors);

        if ($header !== false) {
            // Calculate settlement discount value using settlement % and net value
            return $header;
        }

        return false;
    }

    public function getInvoiceAddress($id = '')
    {
        $invoiceAddress = DataObjectFactory::Factory('Address');
        $invoiceAddress->load($this->inv_address_id);

        return $invoiceAddress;
    }

    public function getInvoiceAddresses($id = '')
    {
        $slmaster_id = '';

        if (! empty($id)) {
            $slmaster_id = $id;
        } elseif ($this->isLoaded()) {
            $slmaster_id = $this->slmaster_id;
        }

        if (! empty($slmaster_id)) {

            $customer = DataObjectFactory::Factory('SLCustomer');
            $customer->load($slmaster_id);

            return $customer->getInvoiceAddresses();
        } else {
            return array();
        }
    }

    public function getDeliveryAddress()
    {
        $invoiceAddress = DataObjectFactory::Factory('Address');
        $invoiceAddress->load($this->del_address_id);

        return $invoiceAddress;
    }

    public function getPersonAddresses($id = '', $data)
    {
        $addresslist = array();
        $addresses = array();

        if (! empty($id) && $id > 0) {

            $addresses = new PersonaddressCollection();

            $sh = new SearchHandler($addresses, false);
            $sh->addConstraint(new Constraint($data['type'], 'is', 'true'));
            $sh->addConstraint(new Constraint('person_id', '=', $id));

            $addresses->load($sh);
        } elseif (! empty($data['slmaster_id'])) {

            $customer = DataObjectFactory::Factory('SLCustomer');
            $customer->load($data['slmaster_id']);

            $addresses = new CompanyaddressCollection();

            $sh = new SearchHandler($addresses, false);
            $sh->addConstraint(new Constraint($data['type'], 'is', 'true'));
            $sh->addConstraint(new Constraint('company_id', '=', $customer->company_id));

            $addresses->load($sh);
        }

        foreach ($addresses as $address) {
            $addresslist[$address->id] = $address->address;
        }

        return $addresslist;
    }

    public function getPeople($id = '')
    {
        if (! empty($id)) {

            $customer = DataObjectFactory::Factory('SLCustomer');
            $customer->load($id);

            $people = new PersonCollection();

            $sh = new SearchHandler($people, false);
            $sh->addConstraint(new Constraint('company_id', '=', $customer->company_id));
            $sh->setFields(array(
                'id',
                'name'
            ));
            $sh->setOrderby('name');

            $people->load($sh);

            $list = array(
                '' => 'None'
            );
            $list += $people->getAssoc();

            return $list;
        } else {
            return array(
                '' => 'None'
            );
        }
    }

    public function getSettlementTerms()
    {
        $payterms = DataObjectFactory::Factory('PaymentTerm');

        if ($payterms->load($this->payment_term_id)) {

            $terms = ($payterms->days == 0) ? $payterms->months . ' months' : $payterms->days . ' days';
            $terms .= ($payterms->basis == 'I') ? ' from Invoice.' : ' from Month End.';

            if ($this->settlement_discount > 0) {
                $terms .= chr(10) . 'A discount of ' . $this->settlement_discount . ' ' . $this->currency . ' can be deducted if paid before ';
            } else {
                $terms .= chr(10) . 'Please make payment in full by ';
            }

            $terms .= un_fix_date($this->due_date);

            return $terms;
        } else {
            return '';
        }
    }

    public function newStatus()
    {
        return 'N';
    }

    public function openStatus()
    {
        return 'O';
    }

    public function queryStatus()
    {
        return 'Q';
    }

    public function paidStatus()
    {
        return 'P';
    }

    public function post(&$errors = array())
    {
        $db = DB::Instance();
        $db->StartTrans();

        // reload the invoice to refresh the dependencies
        $this->load($this->id);

        if ($this->moveStock($errors)) {

            if (parent::post($errors)) {
                return $db->CompleteTrans();
            }
        }

        $db->FailTrans();
        $db->CompleteTrans();

        return false;
    }

    private function moveStock(&$errors = array())
    {
        $customer = DataObjectFactory::Factory('SLCustomer');
        $customer = $customer->load($this->slmaster_id);

        if ($customer) {

            $stock_errors = false;

            foreach ($this->lines as $line) {

                if ($line->move_stock == 't' && ! is_null($line->stitem_id)) {

                    $data = array();

                    if ($this->transaction_type == 'I') {
                        $data['qty'] = $line->sales_qty;
                        $data['process_name'] = 'SI';
                    } else {
                        // Reverse the transaction for a Credit Note
                        $data['qty'] = bcmul($line->sales_qty, - 1);
                        $data['process_name'] = 'S' . $this->transaction_type;
                    }

                    $data['process_id'] = $this->id;
                    $data['stitem_id'] = $line->stitem_id;

                    if (!is_null($this->despatch_action)) {
                        $data['whaction_id'] = $this->despatch_action;
                    } elseif (is_null($line->sales_order_id)) {
                        $data['whaction_id'] = $customer->despatch_action;
                    } else {
                        $sorder = DataObjectFactory::Factory('SOrder');
                        $sorder->load($line->sales_order_id);
                        $data['whaction_id'] = $sorder->despatch_action;
                    }

                    $result = false;

                    if (STTransaction::getTransferLocations($data, $errors)) {

                        $st_errors = array();
                        $models = STTransaction::prepareMove($data, $st_errors);

                        if (count($st_errors) === 0) {

                            foreach ($models as $model) {

                                $result = $model->save($st_errors);

                                if ($result === false) {
                                    $stock_errors = true;
                                }
                            }
                        }

                        if (count($st_errors) > 0) {
                            $errors = array_merge($errors, $st_errors);
                        }
                    } else {
                        $errors[] = 'Error getting transfer locations';
                        return false;
                    }
                }
            }

            if ($stock_errors) {
                $errors[] = 'Error updating stock';
                return false;
            }

            return true;
        }

        $errors[] = 'Failed to load Customer details';
        return false;
    }

    public function save($modelName = null, $dataIn = [], &$errors = [])
    {
        $si_line = DataObjectFactory::Factory('SInvoiceLine');

        $cc = new ConstraintChain();
        $cc->add(new Constraint('invoice_id', '=', $this->id));

        $totals = $si_line->getSumFields(array(
            'gross_value',
            'tax_value',
            'net_value',
            'twin_gross_value',
            'twin_tax_value',
            'twin_net_value',
            'base_gross_value',
            'base_tax_value',
            'base_net_value'
        ), $cc, 'si_lines');

        unset($totals['numrows']);

        // set the correct totals back to the order header
        foreach ($totals as $field => $value) {
            $this->$field = (empty($value)) ? '0.00' : bcadd($value, 0);
        }

        $this->settlement_discount = bcadd($this->getSettlementDiscount(), 0);

        return parent::save();
    }

    public function vatAnalysis()
    {
        $analysis = array();
        $tax_status = DataObjectFactory::Factory('TaxStatus');

        if ($tax_status->load($this->tax_status_id) && $tax_status->apply_tax) {

            foreach ($this->lines as $line) {

                if (isset($analysis[$line->tax_rate_id])) {
                    $analysis[$line->tax_rate_id]['net_value'] = bcadd($line->net_value, $analysis[$line->tax_rate_id]['net_value']);
                    $analysis[$line->tax_rate_id]['tax_value'] = bcadd($line->tax_value, $analysis[$line->tax_rate_id]['tax_value']);
                } else {

                    $tax_rate = DataObjectFactory::Factory('TaxRate');

                    if ($tax_rate->load($line->tax_rate_id)) {
                        $analysis[$line->tax_rate_id]['description'] = $tax_rate->description;
                        $analysis[$line->tax_rate_id]['tax_rate'] = $line->tax_rate_percent;
                        $analysis[$line->tax_rate_id]['net_value'] = bcadd($line->net_value, 0);
                        $analysis[$line->tax_rate_id]['tax_value'] = bcadd($line->tax_value, 0);
                        $analysis[$line->tax_rate_id]['currency'] = $this->currency;
                    }
                }
            }
        }

        return $analysis;
    }

    public function getInvoiceExportList($_definition_id = '')
    {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('transaction_type', '=', 'I'));
        $cc->add(new Constraint('status', '=', 'O'));
        $cc->add(new Constraint('despatch_date', 'is not', 'NULL'));
        $cc->add(new Constraint('print_count', '=', '0'));
        $cc->add(new Constraint('invoice_method', '=', 'D'));
        $cc->add(new Constraint('edi_invoice_definition_id', '=', $_definition_id));

        $this->orderby = 'invoice_number';

        return $this->getAll($cc, false, true);
    }

    public function getDeliveryNote()
    {
        $si_lines = DataObjectFactory::Factory('SInvoiceLine');
        $si_lines->idField = 'delivery_note';
        $si_lines->identifierField = 'delivery_note';

        $cc = new ConstraintChain();
        $cc->add(new Constraint('invoice_id', '=', $this->id));

        $dn = $si_lines->getAll($cc);

        if (count($dn) > 0) {
            return implode(' ', $dn);
        } else {
            return '';
        }
    }

    public function getNextLineNumber($_invoiceline = null)
    {
        $sinvoiceline = DataObjectFactory::Factory('SInvoiceLine');
        return parent::getNextLineNumber($sinvoiceline);
    }

    public function save_model($data)
    {
        // Used to save Invoice Header and Invoice Lines from import or copy of existing
        $flash = Flash::Instance();

        if (empty($data['SInvoice']) || empty($data['SInvoiceLine'])) {
            $flash->addError('Error trying to save invoice');
            return false;
        }

        $errors = array();

        $db = DB::Instance();
        $db->StartTrans();

        $header = $data['SInvoice'];

        $lines_data = DataObjectCollection::joinArray($data['SInvoiceLine'], 0);

        if (! $lines_data || empty($lines_data)) {
            $lines_data[] = $data['SInvoiceLine'];
        }

        $invoice = SInvoice::Factory($header, $errors);

        if (! $invoice || count($errors) > 0) {
            $errors[] = 'Invoice validation failed';
        } elseif (! $invoice->save()) {
            $errors[] = 'Invoice creation failed';
        }

        if ($invoice) {
            foreach ($lines_data as $line) {
                $line['invoice_id'] = $invoice->{$invoice->idField};
                $invoiceline = SInvoiceLine::Factory($invoice, $line, $errors);
                if (! $invoiceline || count($errors) > 0) {
                    $errors[] = 'Invoice Line validation failed for line ' . $line['line_number'];
                } elseif (! $invoiceline->save()) {
                    $errors[] = 'Invoice Line creation failed for line ' . $line['line_number'];
                }
            }
        }

        if (count($errors) === 0) {
            if (! $invoice->save()) {
                $errors[] = 'Error updating Invoice totals';
            } else {
                $result = array(
                    'internal_id' => $invoice->{$invoice->idField},
                    'internal_identifier_field' => $invoice->identifierField,
                    'internal_identifier_value' => $invoice->getidentifierValue()
                );
            }
        }

        if (count($errors) > 0) {
            $flash->addErrors($errors);
            $db->FailTrans();
            $result = false;
        }

        $db->CompleteTrans();

        return $result;
    }

    public function transactionFactory()
    {
        $db = DB::Instance();

        $transaction = DataObjectFactory::Factory('SLTransaction');

        $transaction->{$transaction->idField} = $db->GenID('SLTransactions_id_seq');

        return $transaction;
    }

    protected function get_ledger_control_account($gl_params = null, &$errors = array())
    {
        if (! ($gl_params instanceof GLParams)) {
            $gl_params = DataObjectFactory::Factory('GLParams');
        }

        $glaccount_id = $gl_params->sales_ledger_control_account();

        if ($glaccount_id === false) {
            $errors[] = 'Ledger Control Account Code not found';
        }

        return $glaccount_id;
    }
}

// end of SInvoice.php
