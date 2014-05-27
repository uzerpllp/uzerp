<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PLSupplier extends DataObject
{
	
	protected $version = '$Revision: 1.41 $';
	
	protected $defaultDisplayFields = array('name'
											,'outstanding_balance'
											,'currency'
											,'remittance_advice'
											,'order_method'
											,'payment_type'
											,'payment_term');
	
	public $agedBalances = array();
	
	function __construct($tablename = 'plmaster')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'name';
				
		$this->orderby			= 'name';
		
// Define relationships
		$this->belongsTo('Company', 'company_id', 'name');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('CBAccount', 'cb_account_id', 'bank_account');
 		$this->belongsTo('PaymentTerm', 'payment_term_id', 'payment_term');
 		$this->belongsTo('TaxStatus', 'tax_status_id', 'tax_status'); 
 		$this->belongsTo('WHAction', 'receive_action', 'receive_into'); 
 		$this->belongsTo('PaymentType', 'payment_type_id', 'payment_type');
		$this->belongsTo('DeliveryTerm', 'delivery_term_id', 'delivery_term');
 		$this->hasMany('POrder', 'orders', 'plmaster_id', NULL, false);
		$this->hasMany('PInvoice', 'invoices', 'plmaster_id', NULL, false);
		$this->hasMany('PLTransaction', 'transactions', 'plmaster_id', NULL, false);
		$this->hasOne('Company', 'company_id', 'companydetail');
 		$this->hasOne('PartyContactMethod', 'email_order_id', 'email_order');
 		$this->hasOne('PartyContactMethod', 'email_remittance_id', 'email_remittance');
 		$this->hasOne('Currency', 'currency_id', 'currency_detail');
 		
		$this->setComposite('Address', 'payment_address_id', 'payment_address', array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'countrycode'));
		
 		$cc = new ConstraintChain();
 		$cc->add(new Constraint('order_method', '==', '"E"'));
		$this->addValidator(new DependencyValidator($cc,array('email_order_id', 'email_remittance_id')));

// Define field formats
		$this->getField('outstanding_balance')->setFormatter(new NumericFormatter());
		
// Define system defaults
		$params = DataObjectFactory::Factory('GLParams');
 		$this->getField('currency_id')->setDefault($params->base_currency());
		
// Define enumerated types
 		$this->setEnum('order_method'
			,array('P'=>'Print'
				  ,'F'=>'Fax'
				  ,'E'=>'Email'
				  ,'D'=>'EDI'
				)
		);
		
	}
	
	public function isCurrent()
	{
		if (!is_null($this->date_inactive)) { return FALSE; }
		
	}
	
	public function canDelete()
	{
		// Check that no orders/invoices have been raised
		$collection = new POrderCollection();
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return FALSE; }
		
		$collection = new PInvoiceCollection();
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return FALSE; }
		
		$collection = new PLTransactionCollection();
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return FALSE; }
		
		return TRUE;
		
	}
	
	public function hasCurrentActivity()
	{
		$db = DB::Instance();
		
		// Check that no orders/invoices have been raised
		$model		= DataObjectFactory::Factory('POrder');
		
		$collection = new POrderCollection($model);
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', 'NOT IN', '(' . $db->qstr($model->cancelStatus()) . ', ' . $db->qstr($model->invoiceStatus()) . ')'));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return TRUE; }
		
		$model		= DataObjectFactory::Factory('PInvoice');
		
		$collection = new PInvoiceCollection($model);
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', '!=', $model->paidStatus()));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return TRUE; }
		
		$model		= DataObjectFactory::Factory('PLTransaction');
		
		$collection = new PLTransactionCollection($model);
		
		$sh = new SearchHandler($collection, FALSE);
		
		$sh->addConstraint(new Constraint('plmaster_id', '=', $this->id));
		$sh->addConstraint(new Constraint('status', '!=', $model->paid()));
		
		if (count($collection->load($sh, null, RETURN_ROWS)) > 0) { return TRUE; }
		
		return FALSE;
		
	}
	
	public function getBillingAddress()
	{
		$address = DataObjectFactory::Factory('Address');
		
		$address->load($this->payment_address_id);
		
		return $address;
		
	}
	
	public function getRemittanceAddresses()
	{
		
		$address = DataObjectFactory::Factory('Companyaddress');
		
		$address->IdentifierField = 'address';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('payment', 'is', 'true'));
		
		$addresses = $address->getAddresses($this->company_id, $cc);
		
		return $addresses;

	}

	public function email_order ()
	{
		return $this->email_order->contact;
	}
	
	public function email_remittance ()
	{
		return $this->email_remittance->contact;
	}
	
	public function phone()
	{
		
	}
	
	public function email()
	{
	
	}
	
	public function fax()
	{
	
	}
	
	public function contact()
	{
	
	}
	
	public function outstanding_balance()
	{
		$db = DB::Instance();
		
		$query = "select COALESCE(sum(os_value),0) FROM pltransactions WHERE status='O' AND plmaster_id=".$db->qstr($this->id);
		
		$amount = $db->GetOne($query);
		
		if($amounts===false)
		{
			return false;
		}
		
		return $amount;
	}
	
	public function updateBalance(PLTransaction $pltrans)
	{
		$cc = new ConstraintChain();
		
		$id = $this->{$this->idField};
		
		$cc->add(new Constraint('plmaster_id', '=', $id));
		
		$amount = $pltrans->outstandingBalance($cc);
		
		if ($this->update($id, 'outstanding_balance', $amount))
		{
			return true;
		}
		
		return false;
		
	}
	
	public function getAll(ConstraintChain $cc=null, $ignore_tree=false, $use_collection=false, $limit='', $active = true)
	{
		
		if (!($cc instanceof ConstraintChain))
		{
			$cc = new ConstraintChain();
		}
		
		if ($active === TRUE)
		{
			$cc->add(new Constraint('date_inactive', 'IS', 'NULL'));
		}
		elseif ($active === FALSE)
		{
			$cc->add(new Constraint('date_inactive', 'IS NOT', 'NULL'));
		}
		
		return parent::getAll($cc, $ignore_tree, true, $limit);
	}
	
	public function getCentres($glaccount_id)
	{
// Returns Centre list for a specified Account
		$account = DataObjectFactory::Factory('GLAccount');
		
		$account->load($glaccount_id);
		
		return $account->getCentres();
	}
	
	public function getUnassignedCompanies()
	{

		$ledgercategory		= DataObjectFactory::Factory('LedgerCategory');
		
		return $ledgercategory->getUnassignedCompanies($this);
		
	}
	
}

// End of PLSupplier
