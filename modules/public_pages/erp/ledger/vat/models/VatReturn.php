<?php
/**
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * uzERP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 */
class VatReturnStorageException extends Exception {}


class VatReturn extends DataObject
{
    public $tax_period_closed;
    
    public $gl_period_closed;
    
    protected $defaultDisplayFields = [
        'year',
        'tax_period',
        'vat_due_sales', //Box 1 - VAT Due On Sales
        'vat_due_acquisitions', //Box 2 - VAT Due On EU Purchases
        'total_vat_due', // Box 3 - Output Tax - CHECK THIS!!
        'vat_reclaimed_curr_period', //Box 4 - Input Tax - CHECK THIS!!
        'net_vat_due', //Box 5 - Net Tax
        'total_value_sales_ex_vat', //Box 6 - Sales Exc. VAT
        'total_value_purchase_ex_vat', //Box 7 - Purchases Exc. VAT
        'total_value_goods_supplied_ex_vat', //Box 8 - EU Sales Exc. VAT
        'total_acquisitions_ex_vat', //Box 9 -EU Purchases Exc. VAT
        'tax_period_closed',
        'finalised' => 'submitted'
    ];


    public function __construct($tablename='vat_return') {
        parent::__construct($tablename);
        $this->idField='id';
        $this->orderby = ['year', 'tax_period'];
        $this->orderdir = ['desc', 'desc'];
        // Ensure that these are not shown in the available fields list
        // as they are not available in the collection DB view
        $hidden_fields = [
            'processing_date',
            'period_key',
            'charge_ref_number',
            'form_bundle',
            'payment_indicator',
            'receipt_id_header'
        ];
        foreach ($hidden_fields as $field_name) {
            $this->setHidden($field_name);
        }
        // Add this field object, _data is loaded from the collection
        // but it does not exist in the vat_return table
        $this->setAdditional('tax_period_closed', 'boolean');
        // Set the red/green boolean display formatter
        $this->getField('tax_period_closed')->setFormatter(new BooleanFormatter());
        // Output to smarty from the formatter will be html,
        // flag as html to prevent escaping
        $this->getField('tax_period_closed')->type = 'html';

        // Define field formats		
		$this->getField('vat_due_sales')->setFormatter(new NumericFormatter());
        $this->getField('vat_due_acquisitions')->setFormatter(new NumericFormatter());
        $this->getField('total_vat_due')->setFormatter(new NumericFormatter());
        $this->getField('vat_reclaimed_curr_period')->setFormatter(new NumericFormatter());
        $this->getField('net_vat_due')->setFormatter(new NumericFormatter());
        $this->getField('total_value_sales_ex_vat')->setFormatter(new NumericFormatter());
        $this->getField('total_value_purchase_ex_vat')->setFormatter(new NumericFormatter());
        $this->getField('total_value_goods_supplied_ex_vat')->setFormatter(new NumericFormatter());
        $this->getField('total_acquisitions_ex_vat')->setFormatter(new NumericFormatter());
    }


    public function cb_loaded() {
        // Set the field value, used when displaying the smarty data_table
        $this->getfield('tax_period_closed')->value = $this->_data['tax_period_closed'];

        // Convert the UTC processing time from HMRC to the local timezone
        if ($this->_data['processing_date']) {
            $utc_date = date_create($this->_data['processing_date'], new DateTimeZone('UTC'));
            $this->getfield('processing_date')->value = $utc_date->setTimeZone(new DateTimeZone(date_default_timezone_get()))->format('Y-m-d H:i:s.u');
        }
    }


    /**
     * @param string $year
     * @param string $tax_period
     * @throws VatReturnStorageException
     */
    public function loadVatReturn($year, $tax_period) {
        $q_cc = new ConstraintChain();
        $q_cc->add(new Constraint('year', '=', $year));
        $q_cc->add(new Constraint('tax_period', '=', $tax_period));
        $q_cc->add(new Constraint('usercompanyid', '=', EGS_COMPANY_ID));

        $this->loadBy($q_cc);
        if (!$this->isLoaded()) {
            throw new VatReturnStorageException("Failed to load VAT Return for {$year}/{$tax_period}");
        }
    }


    /**
     * @param string $year
     * @param string $tax_period
     * @throws VatReturnStorageException
     */
    public function newVatReturn($year, $tax_period) {
        try
        {
            $this->loadVatReturn($year, $tax_period);
            if ($this->isLoaded()) {
                //exists
                return;
            }
        }
        catch (VatReturnStorageException $e)
        {
            $this->id = 'NULL';
            $this->year = $year;
            $this->tax_period = $tax_period;
            $this->vat_due_sales = 0;
            $this->vat_due_acquisitions = 0;
            $this->total_vat_due = 0;
            $this->vat_reclaimed_curr_period = 0;
            $this->net_vat_due = 0;
            $this->total_value_sales_ex_vat = 0;
            $this->total_value_purchase_ex_vat = 0;
            $this->total_value_goods_supplied_ex_vat = 0;
            $this->total_acquisitions_ex_vat = 0;
            $this->usercompanyid = EGS_COMPANY_ID;
            $this->finalised = false;
            $dt = new DateTime();
            $this->created = $dt->format('Y-m-d H:i:s.u'); // 'u' will be 000000 prior to php7.2
            $this->createdby = EGS_USERNAME;
            unset($this->created);
            if (!$this->save()) {
                throw new VatReturnStorageException("Failed to Create VAT Return for {$year}/{$tax_period}");
            }
        }
    }


    /**
     * @param string $year
     * @param string $tax_period
     * @param array $boxes computed VAT figures for each 'box'
     * @throws VatReturnStorageException
     */
    public function updateVatReturnBoxes($year, $tax_period, $boxes) {
        $this->loadVatReturn($year, $tax_period);

        $this->vat_due_sales = $boxes['Box1'];
        $this->vat_due_acquisitions = $boxes['Box2'];
        $this->total_vat_due = $boxes['Box3'];
        $this->vat_reclaimed_curr_period = $boxes['Box4'];
        $this->net_vat_due = $boxes['Box5'];
        $this->total_value_sales_ex_vat = $boxes['Box6'];
        $this->total_value_purchase_ex_vat = $boxes['Box7'];
        $this->total_value_goods_supplied_ex_vat = $boxes['Box8'];
        $this->total_acquisitions_ex_vat = $boxes['Box9'];
        $dt = new DateTime();
        $this->lastupdated = $dt->format('Y-m-d H:i:s.u'); // 'u' will be 000000 prior to php7.2
        $this->lastupdatedby = EGS_USERNAME;

        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to update VAT Return for {$year}/{$tax_period}");
        }
    }


    /**
     * Save submission details returned by the MTD api
     *
     * @param string $year
     * @param string $tax_period
     * @param array $details values returned by the MTD api
     * @throws VatReturnStorageException
     */
    public function saveSubmissionDetail($year, $tax_period, $details) {
        $this->loadVatReturn($year, $tax_period);

        $this->processing_date = $details['processingDate'];
        $this->payment_indicator = $details['paymentIndicator'];
        $this->form_bundle = $details['formBundleNumber'];
        $this->charge_ref_number = $details['chargeRefNumber'];
        $this->receipt_id_header = $details['Receipt-ID'];
        $this->finalised = true;
        $dt = new DateTime();
        $this->lastupdated = $dt->format('Y-m-d H:i:s.u'); // 'u' will be 000000 prior to php7.2
        $this->lastupdatedby = EGS_USERNAME;

        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to update VAT Return submission detail for {$year}/{$tax_period}");
        }
    }


    /**
     * @param string $year
     * @param string $tax_period
     * @param string $key tax period key returned by the MTD api
     * @throws VatReturnStorageException
     */
    public function setVatReturnPeriodKey($year, $tax_period, $key) {
        $this->loadVatReturn($year, $tax_period);

        $this->period_key = $key;
        $dt = new DateTime();
        $this->lastupdated = $dt->format('Y-m-d H:i:s.u'); // 'u' will be 000000 prior to php7.2
        $this->lastupdatedby = EGS_USERNAME;

        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to set period key for VAT Return {$year}/{$tax_period}");
        }
    }


    /**
     * Set the Tax and GL period status attributes on this model
     *
     * @param string $year
     * @param string $tax_period
     * @throws VatReturnStorageException
     */
    public function getTaxPeriodStatus ($tax_period=null, $year=null)
    {
        if(is_null($tax_period) && is_null($year) && $this->isLoaded()) {
            $year = $this->year;
            $tax_period = $this->tax_period;
        }
        $this->tax_period_closed = false;
        $this->gl_period_closed = false;
        $glperiod = DataObjectFactory::Factory('GLPeriod');
        $glperiod->getTaxPeriodEnd($tax_period, $year);
        if ($glperiod) {
            $this->tax_period_closed = $glperiod->tax_period_closed;
            $this->gl_period_closed  = $glperiod->closed;
        } else {
            throw new VatReturnStorageException("Failed to get period status for {$year}/{$tax_period}");
        }
    }
}
?>