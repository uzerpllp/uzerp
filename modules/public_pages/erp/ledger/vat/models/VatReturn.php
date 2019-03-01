<?php

class VatReturnStorageException extends Exception {}
class VatReturnException extends Exception {}

class VatReturn extends DataObject
{
    public $tax_period_closed;
    
    public $gl_period_closed;
    
    protected $defaultDisplayFields = [
        year,
        tax_period,
        vat_due_sales, //Box 1 - VAT Due On Sales
        vat_due_aquisitions, //Box 2 - VAT Due On EU Purchases
        total_vat_due, // Box 3 - Output Tax - CHECK THIS!!
        vat_reclaimed_curr_period, //Box 4 - Input Tax - CHECK THIS!!
        net_vat_due, //Box 5 - Net Tax
        total_value_sales_ex_vat, //Box 6 - Sales Exc. VAT
        total_value_purchase_ex_vat, //Box 7 - Purchases Exc. VAT
        total_value_goods_supplied_ex_vat, //Box 8 - EU Sales Exc. VAT
        total_aquisitions_ex_vat, //Box 9 -EU Purchases Exc. VAT
        tax_period_closed,
        finalised
    ];

    public function __construct($tablename='vat_return') {
        parent::__construct($tablename);
        $this->idField='id';
        $this->orderby = ['year', 'tax_period'];
        $this->orderdir = ['desc', 'desc'];
    }

    /**
     * @param string $year
     * @param string $tax_period
     * @throws VatReturnStorageException
     */
    public function newVatReturn($year, $tax_period) {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $this->loadBy($cc);
        if ($this->isLoaded()) {
            // VAT return already exists
            return;
        }

        $this->id = 'NULL';
        $this->year = $year;
        $this->tax_period = $tax_period;
        $this->usercompanyid = EGS_COMPANY_ID;
        $this->finalised = false;
        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to Create VAT Return for {$year}/{$tax_period}");
        }
    }

    public function loadVatReturn($year, $tax_period) {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $this->loadBy($cc);
        if (!$this->isLoaded()) {
            throw new VatReturnException("Failed to load VAT Return for {$year}/{$tax_period}");
        }
    }

    public function updateVatReturnBoxes($year, $tax_period, $boxes, $finalise=false) {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $this->loadBy($cc);
        if (!$this->isLoaded()) {
            throw new VatReturnException("Failed to load VAT Return for {$year}/{$tax_period}");
        }

        $this->vat_due_sales = $boxes['Box1'];
        $this->vat_due_aquisitions = $boxes['Box2'];
        $this->total_vat_due = $boxes['Box3'];
        $this->vat_reclaimed_curr_period = $boxes['Box4'];
        $this->net_vat_due = $boxes['Box5'];
        $this->total_value_sales_ex_vat = $boxes['Box6'];
        $this->total_value_purchase_ex_vat = $boxes['Box7'];
        $this->total_value_goods_supplied_ex_vat = $boxes['Box8'];
        $this->total_aquisitions_ex_vat = $boxes['Box9'];
        if ($finalise === true) {
            $this->finalised = true;
        }

        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to update VAT Return for {$year}/{$tax_period}");
        }
    }


    public function setVatReturnPeriodKey($year, $tax_period, $key) {
        $cc = new ConstraintChain();
        $cc->add(new Constraint('year', '=', $year));
        $cc->add(new Constraint('tax_period', '=', $tax_period));
        $this->loadBy($cc);
        if (!$this->isLoaded()) {
            throw new VatReturnException("Failed to load VAT Return for {$year}/{$tax_period}");
        }

        $this->period_key = $key;

        if (!$this->save()) {
            throw new VatReturnStorageException("Failed to update VAT Return for {$year}/{$tax_period}");
        }
    }

    public function getTaxPeriodStatus ($tax_period, $year)
	{
		$this->tax_period_closed = false;
		$this->gl_period_closed = false;
		$glperiod = DataObjectFactory::Factory('GLPeriod');
		$glperiod->getTaxPeriodEnd($tax_period, $year);
		if ($glperiod)
		{
			$this->tax_period_closed = $glperiod->tax_period_closed;
			$this->gl_period_closed  = $glperiod->closed;
		}
		else
		{
			throw new VatReturnException("Failed to get period status for {$year}/{$tax_period}");
		}
	}
}
?>