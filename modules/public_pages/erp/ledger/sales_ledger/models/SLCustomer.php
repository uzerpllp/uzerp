<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class SLCustomer extends DataObject
{

    protected $version = '$Revision: 1.62 $';

    protected $defaultDisplayFields = array(
        'name',
        'outstanding_balance',
        'currency',
        'statement',
        'invoice_method',
        'payment_type',
        'payment_term',
        'sl_analysis'
    );

    function __construct($tablename = 'slmaster')
    {
        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'name';

        $this->orderby = 'name';

        // Define relationships
        $this->belongsTo('Company', 'company_id', 'name');
        $this->belongsTo('Currency', 'currency_id', 'currency');
        $this->belongsTo('CBAccount', 'cb_account_id', 'bank_account');
        $this->belongsTo('PaymentTerm', 'payment_term_id', 'payment_term');
        $this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status');
        $this->belongsTo('SLAnalysis', 'sl_analysis_id', 'sl_analysis');
        $this->belongsTo('PaymentType', 'payment_type_id', 'payment_type');
        $this->belongsTo('SOPriceType', 'so_price_type_id', 'so_price_type');
        $this->belongsTo('DeliveryTerm', 'delivery_term_id', 'delivery_term');
        $this->belongsTo('DataDefinition', 'edi_invoice_definition_id', 'edi_invoice_definition');
        $this->hasMany('SOrder', 'orders', 'slmaster_id', NULL, false);
        $this->hasMany('SInvoice', 'invoices', 'slmaster_id', NULL, false);
        $this->hasMany('SLTransaction', 'transactions', 'slmaster_id', NULL, false);
        $this->hasMany('SLDiscount', 'discounts', 'slmaster_id');
        $this->hasMany('SOProductLine', 'products', 'slmaster_id');
        $this->hasOne('Company', 'company_id', 'companydetail');
        $this->hasOne('CBAccount', 'cb_account_id', 'bank_account_detail');
        $this->hasOne('PartyContactMethod', 'email_invoice_id', 'email_invoice');
        $this->hasOne('PartyContactMethod', 'email_statement_id', 'email_statement');
        $this->hasOne('Currency', 'currency_id', 'currency_detail');
        $this->hasOne('WHAction', 'despatch_action', 'despatch_from');

        $this->setComposite('Address', 'billing_address_id', 'billing_address', array(
            'street1',
            'street2',
            'street3',
            'town',
            'county',
            'postcode',
            'countrycode'
        ));

        $cc = new ConstraintChain();
        $cc->add(new Constraint('invoice_method', '==', '"E"'));
        $this->addValidator(new DependencyValidator($cc, array(
            'email_invoice_id',
            'email_statement_id'
        )));

        // Define field formats
        $this->getField('outstanding_balance')->setFormatter(new NumericFormatter());

        // Define system defaults
        $params = DataObjectFactory::Factory('GLParams');
        $this->getField('currency_id')->setDefault($params->base_currency());
        $this->getField('account_status')->setDefault($this->openStatus());

        // Define enumerated types
        $this->setEnum('invoice_method', array(
            'P' => 'Print',
            'F' => 'Fax',
            'E' => 'Email',
            'D' => 'EDI'
        ));
        $this->setEnum('account_status', array(
            'O' => 'Open',
            'S' => 'Stopped'
        ));
    }

    public function accountStopped()
    {
        return ($this->account_status == 'S');
    }

    public function openStatus()
    {
        return 'O';
    }

    public function stopStatus()
    {
        return 'S';
    }

    public function canDelete()
    {
        // Check that no orders/invoices have been raised
        $collection = new SOrderCollection();

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return FALSE;
        }

        $collection = new SInvoiceCollection();

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return FALSE;
        }

        $collection = new SLTransactionCollection();

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return FALSE;
        }

        return TRUE;
    }

    public function hasCurrentActivity()
    {
        $db = DB::Instance();

        // Check that no orders/invoices have been raised
        $model = DataObjectFactory::Factory('SOrder');

        $collection = new SOrderCollection($model);

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));
        $sh->addConstraint(new Constraint('status', 'NOT IN', '(' . $db->qstr($model->cancelStatus()) . ', ' . $db->qstr($model->invoiceStatus()) . ')'));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return TRUE;
        }

        $model = DataObjectFactory::Factory('SInvoice');

        $collection = new SInvoiceCollection($model);

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));
        $sh->addConstraint(new Constraint('status', '!=', $model->paidStatus()));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return TRUE;
        }

        $model = DataObjectFactory::Factory('SLTransaction');

        $collection = new SLTransactionCollection($model);

        $sh = new SearchHandler($collection, FALSE);

        $sh->addConstraint(new Constraint('slmaster_id', '=', $this->id));
        $sh->addConstraint(new Constraint('status', '!=', $model->paid()));

        if (count($collection->load($sh, null, RETURN_ROWS)) > 0) {
            return TRUE;
        }

        return FALSE;
    }

    public function getBillingAddress($id = '')
    {
        $address = DataObjectFactory::Factory('Address');

        $address->load($this->billing_address_id);

        return $address;
    }

    public function getDeliveryAddress($id)
    {
        $address = DataObjectFactory::Factory('Address');

        $address->load($id);

        return $address;
    }

    public function getDeliveryAddresses()
    {
        $address = DataObjectFactory::Factory('Companyaddress');

        $address->IdentifierField = 'address';

        $cc = new ConstraintChain();

        $cc->add(new Constraint('shipping', 'is', 'true'));

        $addresses = $address->getAddresses($this->company_id, $cc);

        return $addresses;
    }

    public function getInvoiceAddresses()
    {
        $address = DataObjectFactory::Factory('Companyaddress');

        $address->IdentifierField = 'address';

        $cc = new ConstraintChain();

        $cc->add(new Constraint('billing', 'is', 'true'));

        $addresses = $address->getAddresses($this->company_id, $cc);

        return $addresses;
    }

    function email_invoice()
    {
        return $this->email_invoice->contact;
    }

    function email_statement()
    {
        return $this->email_statement->contact;
    }

    function transaction_count()
    {
        $transaction = DataObjectFactory::Factory('SLTransaction');

        $cc = new ConstraintChain();
        $cc->add(new Constraint('slmaster_id', '=', $this->{$this->idField}));

        return $transaction->getCount($cc);
    }

    public function accountnumber()
    {
        $company = DataObjectFactory::Factory('Company');

        if ($company->load($this->company_id)) {
            return $company->accountnumber;
        }

        return 'Not Found';
    }

    public function phone()
    {}

    public function email()
    {}

    public function fax()
    {}

    public function contact()
    {}

    public function outstanding_balance()
    {
        $db = DB::Instance();

        $query = "select COALESCE(sum(os_value),0) FROM sltransactions WHERE status='O' AND slmaster_id=" . $db->qstr($this->id);

        $amount = $db->GetOne($query);

        if ($amounts === false) {
            return false;
        }

        return $amount;
    }

    public function updateBalance(SLTransaction $sltrans)
    {
        $cc = new ConstraintChain();

        $id = $this->{$this->idField};

        $cc->add(new Constraint('slmaster_id', '=', $id));

        $amount = $sltrans->outstandingBalance($cc);

        if ($this->update($id, 'outstanding_balance', $amount)) {
            return true;
        }

        return false;
    }

    public function getCentres($glaccount_id)
    {
        // Returns Centre list for a specified Account
        $account = DataObjectFactory::Factory('GLAccount');

        $account->load($glaccount_id);

        return $account->getCentres();
    }

    public function getOutstandingOrders()
    {
        $sh = new SearchHandler(new SOrderCollection(), false);

        $sh->addConstraint(new Constraint('type', '=', 'O'));
        $sh->addConstraint(new Constraint('status', 'NOT IN', "('X','I','H')"));

        $this->addSearchHandler('orders', $sh);

        $value = 0;

        foreach ($this->orders as $order) {
            if ($order->status != 'P') {
                $value += $order->base_net_value;
            } else {
                foreach ($order->lines as $lines) {
                    if (in_array($lines->status, array(
                        'N',
                        'R',
                        'D'
                    ))) {
                        $value += $lines->base_net_value;
                    }
                }
            }
        }

        return $value;
    }

    public function getAll(ConstraintChain $cc = null, $ignore_tree = false, $use_collection = true, $limit = '', $active = true)
    {
        if (! ($cc instanceof ConstraintChain)) {
            $cc = new ConstraintChain();
        }

        if ($active === TRUE) {
            $cc->add(new Constraint('date_inactive', 'IS', 'NULL'));
        } elseif ($active === FALSE) {
            $cc->add(new Constraint('date_inactive', 'IS NOT', 'NULL'));
        }

        return parent::getAll($cc, $ignore_tree, TRUE, $limit);
    }

    public function getAgedDebtorSummary($_aged_months)
    {
        $trans = new SLTransactionCollection();

        return $trans->agedDebtor($this->id, $_aged_months);
    }

    public function getUnassignedCompanies()
    {
        $ledgercategory = DataObjectFactory::Factory('LedgerCategory');

        return $ledgercategory->getUnassignedCompanies($this);
    }

    /*
     * @return integer Party model record ID
     */
    public function getPartyID()
    {
        return $this->companydetail->party_id;
    }
}
