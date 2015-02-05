<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SOrder extends SPOrder { 

	protected $version = '$Revision: 1.75 $';
	
	protected $defaultDisplayFields = array(
		'order_number',
		'customer',
		'person',
		'order_date',
		'despatch_date',
		'status',
		'ext_reference'=>'Customer Reference',
		'type',
		'currency',
		'base_net_value',
		'description',
		'slmaster_id',
		'person_id'
	);
	
	protected $linkRules;
	
	function __construct($tablename = 'so_header')
	{
		
		// Register non-persistent attributes
		$this->setAdditional('tax_value', 'numeric');
		$this->setAdditional('gross_value', 'numeric');
		
		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'order_number';
		$this->view				= '';
		$this->orderby			= array('order_date', 'order_number');
		$this->orderdir			= array('DESC', 'DESC');
		
		$this->validateUniquenessOf('order_number');
		
		// Define relationships
		$this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
		$this->belongsTo('Currency', 'currency_id', 'currency');
		$this->belongsTo('Currency', 'twin_currency_id', 'twin_currency');
		$this->belongsTo('Person', 'person_id', 'person', null, "surname || ', ' || firstname");
		$this->belongsTo('DeliveryTerm', 'delivery_term_id', 'delivery_term');
		$this->belongsTo('Address', 'del_address_id', 'delivery_address');
		$this->belongsTo('Address', 'inv_address_id', 'invoice_address');
		$this->belongsTo('WHAction', 'despatch_action');
		
		$this->hasOne('SLCustomer', 'slmaster_id', 'customerdetails');
		$this->hasOne('Person', 'person_id', 'persondetails');
		$this->hasOne('Address', 'del_address_id', 'del_address');
		$this->hasOne('Address', 'inv_address_id', 'inv_address');
		$this->hasOne('WHAction', 'despatch_action', 'despatch_from');
		
		$this->setComposite('Address', 'del_address_id', 'fulladdress', array('street1', 'street2', 'street3', 'town', 'county', 'postcode', 'countrycode'));
		
		$this->hasMany('SOrderLine','lines','order_id');
		$this->hasMany('SOPackingSlip','packing_slips','order_id');
		$this->hasMany('SInvoice','invoices','sales_order_id');
		$this->hasMany('STTransaction', 'transactions', 'process_id');
		$this->hasMany('MFWorkorder', 'works_orders', 'order_id');
		
		// Define field formats
		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
		// set formatters, more set in load() function
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		
		// Define enumerated types
		$this->setEnum(
			'type',
			array(
				'O'	=> 'Sales Order',
				'Q'	=> 'Quote',
				'T'	=> 'Template',
			)
		);
						
		$this->setEnum(
			'status',
			array(
				'N'	=> 'New',
				'O'	=> 'Open',
				'H'	=> 'Hold',
				'X'	=> 'Cancelled',
				'D'	=> 'Despatched',
				'P'	=> 'Part Despatched',
				'I'	=> 'Invoiced'
			)
		);
		
		// Define hasMany related view rules
		$this->linkRules = array(
			// Do not allow links for lines
			'lines' => array(
					 'actions'	=> array()
					,'rules'	=> array()
			),
			// Do not allow links for invoices
			'invoices' => array(
					 'actions'	=> array()
					,'rules'	=> array()
			),
			'transactions'=>array(
					 'newtab'	=> array('new'=>true)
					,'actions'	=> array('link')
					,'rules'	=> array()
			),
			'works_orders'=>array(
					 'newtab'	=> array('new'=>true)
					,'modules'	=> array('link'=>array('module'=>'manufacturing')
										,'new'=>array('module'=>'manufacturing'))
					,'actions'	=> array('link','new')
					,'rules'	=> array(
					// TODO: Only want this rule to apply to 'New'
//										 array('field'=>'status', 'criteria'=>"=='N'")
										)
			)
		);
		
	}
	
	// fire when the DataObject has loaded
	function cb_loaded($success) 
	{
		
		if (isset($this->_data['currency_id']))
		{
			
			// then set these formatters here because they depend on the loaded currency_id
			$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
			$this->getField('tax_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
			$this->getField('gross_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
			
		}
		
	}

	public static function Factory($header_data, &$errors)
	{

		// Check the Customer
		$customer = DataObjectFactory::Factory('SLCustomer');
		
		$customer->load($header_data['slmaster_id']);
		
		if ($customer->isLoaded())
		{
			$header_data['currency_id']		= $customer->currency_id;
			$header_data['payment_term_id']	= $customer->payment_term_id;
			$header_data['tax_status_id']	= $customer->tax_status_id;
		}
		else
		{
			$errors[] = 'Cannot find Customer';
			return false;
		}
		
		if (empty($header_data['despatch_action']))
		{
			$header_data['despatch_action'] = $customer->despatch_action;
		}
		
		if (empty($header_data['inv_address_id']))
		{
			$header_data['inv_address_id'] = $customer->getBillingAddress()->id;
		}
		
		if (empty($header_data['type']))
		{
			$header_data['type'] = 'O';
		}
		if (empty($header_data['order_date']))
		{
			$header_data['order_date'] = date(DATE_FORMAT);
		}
		
		if (empty($header_data['despatch_date']) && empty($header_data['due_date']))
		{
			$header_data['despatch_date'] = date(DATE_FORMAT);
		}
		
		if (empty($header_data['despatch_date']) && !empty($header_data['due_date']))
		{
			$header_data['despatch_date'] = date(DATE_FORMAT,strtotime((string) fix_date($header_data['due_date']))-1);
		}
		
		if (empty($header_data['due_date']) && !empty($header_data['despatch_date']))
		{
			$header_data['due_date'] = date(DATE_FORMAT,strtotime((string) fix_date($header_data['despatch_date']))+1);
		}
		
		$header_data['net_value'] = $header_data['twin_net_value']=$header_data['base_net_value']=0;
		
		if (!isset($header_data['person_id']) || $header_data['person_id']==-1)
		{
			$header_data['person_id'] = '';
		}
		
		$header = SPOrder::makeHeader($header_data,'SOrder',$errors);
		
		if($header!==false && count($errors)==0)
		{
			return $header;
		}
		return false;
	}
	
	public function getDeliveryAddresses ($id='')
	{
		$slmaster_id='';
		
		if (!empty($id))
		{
			$slmaster_id = $id;
		}
		elseif ($this->isLoaded())
		{
			$slmaster_id = $this->slmaster_id;
		}
		
		if (!empty($slmaster_id))
		{
			$customer = DataObjectFactory::Factory('SLCustomer');
			
			$customer->load($slmaster_id);
			
			return $customer->getDeliveryAddresses();
		}
		else
		{
			return array();
		}
	}	

	public function getInvoiceAddress ($id='')
	{
		$invoiceAddress = DataObjectFactory::Factory('Address');
		
		$invoiceAddress->load($this->inv_address_id);
		
		return $invoiceAddress;
	}

	public function getInvoiceAddresses ($id='')
	{
		$slmaster_id = '';
		
		if (!empty($id)) {
			$slmaster_id = $id;
		}
		elseif ($this->isLoaded())
		{
			$slmaster_id = $this->slmaster_id;
		}
		
		if (!empty($slmaster_id))
		{
			$customer = DataObjectFactory::Factory('SLCustomer');
			
			$customer->load($slmaster_id);
			
			return $customer->getInvoiceAddresses();
		}
		else
		{
			return array();
		}
	}	

	public function getEmail()
	{
		if (!is_null($this->person_id))
		{
			return $this->persondetails->getContactDetail('E');
		} else {
			return $this->customerdetails->companydetail->getContactDetail('E');
		}
		
	}
	
	public function getPhone()
	{
		if (!is_null($this->person_id))
		{
			return $this->persondetails->getContactDetail('T');
		} else {
			return $this->customerdetails->companydetail->getContactDetail('T');
		}
	
	}
	
	public function getMobile()
	{
		if (!is_null($this->person_id))
		{
			return $this->persondetails->getContactDetail('M');
		} else {
			return $this->customerdetails->companydetail->getContactDetail('M');
		}
	
	}
	
	public function getPersonAddresses ($id='', $data)
	{
		
		$addresslist=array();
		
		$addresses=array();
		
		if (!empty($id) && $id>0) {
			$address = DataObjectFactory::Factory('Personaddress');
			$address->identifierField = 'address';
			
			$cc = new ConstraintChain();
			$cc->add(new Constraint($data['type'], 'is', 'true'));
			$cc->add(new Constraint('person_id', '=', $id));

			$addresses = $address->getAll($cc, null, TRUE);
		}
		elseif (!empty($data['slmaster_id']))
		{
			$customer = DataObjectFactory::Factory('SLCustomer');
			$customer->load($data['slmaster_id']);
			
			$address = DataObjectFactory::Factory('Companyaddress');
			$address->identifierField = 'address';
			
			$cc = new ConstraintChain();
			$cc->add(new Constraint($data['type'], 'is', 'true'));
			$cc->add(new Constraint('company_id', '=', $customer->company_id));

			$addresses = $address->getAll($cc, null, TRUE);
		}
		
		return $addresses;
		
	}
	
	public function cancelStatus()
	{
		return 'X';
	}
		
	public function despatchStatus()
	{
		return 'D';
	}
		
	public function invoiceStatus()
	{
		return 'I';
	}
		
	public function newStatus()
	{
		return 'N';
	}
		
	public function openStatus()
	{
		return 'O';
	}
		
	public function partDespatchStatus()
	{
		return 'P';
	}
		
	public function cancelled()
	{
		return ($this->status=='X');
	}
	
	public function despatched()
	{
		return ($this->status=='D');
	}
	
	public function partDespatched()
	{
		return ($this->status=='P');
	}
	
	public function invoiced()
	{
		return ($this->status=='I');
	}
	
	public function sales_order()
	{
		return 'O';
	}
	
	public function save ()
	{
		$linestatuses = $this->getLineStatuses();
		
		$linestatus = $linestatuses['count'];
		
		if (($this->someLinesDespatched($linestatus)
			|| $this->someLinesInvoiced($linestatus))
			&& !$this->allLinesDespatchedOrInvoiced($linestatus))
		{
			$this->status = $this->partDespatchStatus();
		}
		elseif ($this->allLinesCancelled($linestatus))
		{
			$this->status = $this->cancelStatus();
		}
		elseif ($this->allLinesDespatched($linestatus)
				  || ($this->allLinesDespatchedOrInvoiced($linestatus)
				  	  && !$this->allLinesInvoiced($linestatus)))
		{
			$this->status = $this->despatchStatus();
		}
		elseif ($this->allLinesInvoiced($linestatus))
		{
			$this->status = $this->invoiceStatus();
		}
				
		$so_line = DataObjectFactory::Factory('SOrderLine');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('order_id', '=', $this->id));		
		$cc->add(new Constraint('status', '!=', $so_line->cancelStatus()));	
			
		$totals = $so_line->getSumFields(
					array(
						'net_value',
						'twin_net_value',
						'base_net_value'
						),
					$cc,
					'so_lines'
				);
				
		unset($totals['numrows']);
		// set the correct totals back to the order header
		foreach($totals as $field=>$value)
		{
			$this->$field = (empty($value))?0.00:bcadd($value,0);
		}
		
		return parent::save();
	}
	
	public function allLinesCancelled($linestatus)
	{
		if ($linestatus['X']>0 &&
				$this->lines->count()==($linestatus['X']))
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}

	public function allLinesDespatched($linestatus)
	{
		if ($linestatus['D']>0 &&
				$this->lines->count()==($linestatus['D']+$linestatus['X']))
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}
	
	public function allLinesDespatchedOrInvoiced($linestatus)
	{
		if (($linestatus['D']>0 || $linestatus['I']>0) &&
				$this->lines->count()==($linestatus['I']+$linestatus['D']+$linestatus['X']))
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}
	
	public function allLinesInvoiced($linestatus)
	{
		if (($linestatus['I']>0) &&
				$this->lines->count()==($linestatus['I']+$linestatus['X']))
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}
	
	public function allLinesNew($linestatus)
	{
		if (($linestatus['N']>0) &&
				$this->lines->count()==($linestatus['N']+$linestatus['X']))
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}
	
	public function someLinesDespatched($linestatus)
	{
		return ($linestatus['D']>0 );
		
	}
	
	public function someLinesInvoiced($linestatus)
	{
		return ($linestatus['I']>0 );
	}
	
	public function someLinesNew($linestatus)
	{
		return ($linestatus['N']>0 );
	}
	
	public function someLinesPartDespatched($linestatus)
	{
		return ($linestatus['P']>0 );
	}
	
	public function someLinesPicked($linestatus)
	{
		return ($linestatus['S']>0 );
	}
	
	public function Despatch($linestatus)
	{
		if ($linestatus['D']>0 )
		{
			return true;		
		}
		else
		{
			return false;
		}
		
	}
	
	public function someLinesNotNormal($linestatus)
	{
		foreach($linestatus as $key=>$value)
		{
			if($key!='N' && $key!='X' && $value>0)
			{
				return true;
			}			
		}
		return false;		
	}

	public function lineExistsInDespatchLines($line_id)
	{
		/**
		 * if the line exists within so_despatchlines then return its dispatch
		 * note number, if it doesn't exist, return -1
		 */ 
		
		$despatchnote = DataObjectFactory::Factory('SODespatchLine');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('orderline_id', '=', $line_id));
		$cc->add(new Constraint('status', '=', 'N'));
		
		$despatchnote->loadBy($cc);

		if($despatchnote->isLoaded())
		{
			return $despatchnote->despatch_number;	
		}
		else
		{
			return -1;
		}	
	}

	public function status_value ($status)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('order_id', '=', $this->id));
		$cc->add(new Constraint('status', '=', $status));
		
		return $this->getSum('net_value', $cc, 'so_lines');;
	}
	

	public function getLineStatuses() {
		$sorderline = DataObjectFactory::Factory('SOrderLine');
		
		$sorderlines = new SOrderLineCollection($this->orderline);
		
		return parent::getLineStatuses($sorderline, $sorderlines);
	}
	
	public function getNextLineNumber ()
	{
		
		$sorderline = DataObjectFactory::Factory('SOrderLine');
		
		return parent::getNextLineNumber($sorderline);
		
	}
	
	public function save_model($data)
	{
// Used to save Order Header and Order Lines from import or copy of existing
		$flash = Flash::Instance();

		if (empty($data['SOrder']) || empty($data['SOrderLine']))
		{
			$flash->addError('Error trying to save order');
			return false;
		}
		
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$header = $data['SOrder'];

		$lines_data = DataObjectCollection::joinArray($data['SOrderLine'], 0);
		
		if (!$lines_data || empty($lines_data))
		{
			$lines_data[] = $data['SOrderLine'];
		}

		$order = SOrder::Factory($header,$errors);
		
		if (!$order || count($errors)>0)
		{
			$errors[] = 'Order validation failed';
		}
		elseif (!$order->save())
		{
			$errors[] = 'Order creation failed';
		}
		
		foreach ($lines_data as $line)
		{
			$line['order_id'] = $order->{$order->idField};
			
			$orderline = SOrderLine::Factory($order, $line, $errors);
			
			if (!$orderline || count($errors)>0)
			{
				$errors[] = 'Order Line validation failed for line '.$line['line_number'];
			}
			elseif (!$orderline->save())
			{
				$errors[] = 'Order Line creation failed for line '.$line['line_number'];
			}			
		}
		
		if (count($errors)===0)
		{
			if (!$order->save())
			{
				$errors[] = 'Error updating Sales Order totals';
			}
			else
			{
				$result = array('internal_id'=>$order->{$order->idField}, 'internal_identifier_field'=>$order->identifierField, 'internal_identifier_value'=>$order->getidentifierValue());
			}
		}
		
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$db->FailTrans();
			$result = false;
		}
		
		$db->CompleteTrans();
		
		return $result;

	}
	
}

// end of SOrder.php