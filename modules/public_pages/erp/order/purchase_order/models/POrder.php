<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class POrder extends SPOrder {

	protected $version = '$Revision: 1.44 $';
	
	public $order_lines = array();
	private $order_lines_count;
	public $comment;
		
	protected $defaultDisplayFields = array(
		'order_number',
		'supplier',
		'order_date',
		'due_date',
		'status',
		'type',
		'currency',
		'net_value',
		'base_net_value',
		'raised_by_person',
		'description',
		'project',
		'project_id',
		'plmaster_id'
	);

	function __construct($tablename = 'po_header')
	{
		
		// Register non-persistent attributes

		// Contruct the object
		parent::__construct($tablename);

		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'order_number';
		$this->view				= '';
		$this->orderby			= array('order_date', 'order_number');
		$this->orderdir			= array('DESC', 'DESC');
		$this->_title			= 'Purchase Order';
		
		// Define validation
		$this->validateUniquenessOf('order_number');
		
		// Define access
		// Set general access controll off, but still set the control fields
		// as these will be used to determine who can edit a specific order
		$this->setAccessControlled(FALSE, null, array('owner', 'raised_by'));
		
		// Define relationships
 		$this->belongsTo('PLSupplier', 'plmaster_id', 'supplier');
 		$this->belongsTo('Currency', 'currency_id', 'currency');
 		$this->belongsTo('Currency', 'twin_currency_id', 'twin_currency');
 		$this->belongsTo('Companyaddress', 'del_address_id', 'del_address');
 		$this->belongsTo('User', 'raised_by', 'raised_by_person');
 		$this->belongsTo('User', 'authorised_by', 'authorised_by_person');
 		$this->belongsTo('Project', 'project_id', 'project');
 		$this->belongsTo('Task', 'task_id', 'task');
 		$this->belongsTo('SOrder', 'sales_order_id', 'order_id', null, array('order_number', 'customer', 'person'));
		$this->belongsTo('DeliveryTerm', 'delivery_term_id', 'delivery_term');
 		$this->hasMany('POrderLine', 'lines', 'order_id');
		$this->hasMany('PInvoice', 'invoices', 'purchase_order_id');
		$this->hasMany('PInvoiceLine', 'invoice_lines', 'purchase_order_id');
		
		// Define field formats
		$params			= DataObjectFactory::Factory('GLParams');
		$base_currency	= $params->base_currency();
		
		$this->getField('base_net_value')->setFormatter(new CurrencyFormatter($base_currency));
		
		// Define enumerated types
 		$this->setEnum(
 			'type',
 			array(
 				'R'	=> 'Requisition',
				'O'	=> 'Purchase Order',
				'T'	=> 'Template'
			)
 		);
 		
		$this->setEnum(
			'status',
			array(
				'N'	=> 'New',
				'O'	=> 'Order Sent',
				'A'	=> 'Order Acknowledged',
				'H'	=> 'Hold',
				'X'	=> 'Cancelled',
				'R'	=> 'Received',
				'P'	=> 'Part Received',
				'I'	=> 'Invoiced'
			)
		);
		
	}
	
	function cb_loaded()
	{
		
		// then set these formatters here because they depend on the loaded currency_id
		$this->getField('net_value')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
		
	}
	
	public static function Factory($header_data, &$errors)
	{
		
		if (empty($header_data['order_date']))
		{
			$header_data['order_date'] = date(DATE_FORMAT);
		}
		
		if (empty($header_data['due_date'])) {
			$header_data['due_date'] = date(DATE_FORMAT);
		}
		
		$header = SPOrder::makeHeader($header_data, 'POrder', $errors);
		
		if($header!==false)
		{
			return $header;
		}
		
		return false;
	}
	
	public function awaitingDelivery()
	{
		return ($this->status=='O' || $this->status=='A');
	}
	
	public function acknowledgedStatus()
	{
		return 'A';
	}
	
	public function cancelStatus()
	{
		return 'X';
	}
	
	public function invoiceStatus()
	{
		return 'I';
	}
	
	public function newStatus()
	{
		return 'N';
	}
	
	public function orderSentStatus()
	{
		return 'O';
	}
	
	public function partReceivedStatus()
	{
		return 'P';
	}
	
	public function receivedStatus()
	{
		return 'R';
	}
	
	public function acknowledged()
	{
		return ($this->status=='A');
	}
	
	public function cancelled()
	{
		return ($this->status=='X');
	}
	
	public function invoiced()
	{
		return ($this->status=='I');
	}

	public function isNew()
	{
		return ($this->status=='N');
	}

	public function partReceived()
	{
		return ($this->status=='P');
	}
	
	public function Received()
	{
		return ($this->status=='R');
	}
	
	public function orderSent()
	{
		return ($this->status=='O');
	}
	
	public function allLinesAwaitingDelivery($linestatus)
	{
		return ($linestatus['A']>0 &&
				$this->lines->count()==($linestatus['A']+$linestatus['X']));
	}
	
	public function someLinesAwaitingDelivery($linestatus)
	{
		return ($linestatus['A']>0);
	}
	
	public function allLinesCancelled($linestatus)
	{
		return ($linestatus['X']>0 &&
				$this->lines->count()==($linestatus['X']));
	}
	
	public function allLinesInvoiced($linestatus)
	{
		return ($linestatus['I']>0 &&
				$this->lines->count()==($linestatus['I']+$linestatus['X']));
	}

	public function allLinesNew($linestatus)
	{
		return ($linestatus['N']>0 &&
				$this->lines->count()==($linestatus['N']+$linestatus['X']));
	}
	
	public function someLinesNew($linestatus)
	{
		return ($linestatus['N']>0 );
	}
	
	public function someLinesInvoiced($linestatus)
	{
		return ($linestatus['I']>0);
	}
	
	public function someLinesReceived($linestatus)
	{
		return ($linestatus['P']>0 || $linestatus['R']>0);
	}
	
	public function allLinesReceived($linestatus)
	{
		return ($linestatus['R']>0 &&
				$this->lines->count()==($linestatus['R']+$linestatus['X']));
	}
	
	public function allLinesReceivedOrInvoiced($linestatus)
	{
		return (($linestatus['R']>0 || $linestatus['I']>0) &&
				$this->lines->count()==($linestatus['I']+$linestatus['R']+$linestatus['X']));
	}
	
	public function save (&$errors=array())
	{
		$linestatuses	= $this->getLineStatuses();
		$linestatus		= $linestatuses['count'];
		
		if (($this->someLinesReceived($linestatus)
			|| $this->someLinesInvoiced($linestatus))
			&& !$this->allLinesReceivedOrInvoiced($linestatus))
		{
			$this->status = $this->partReceivedStatus();
		}
		elseif ($this->allLinesCancelled($linestatus))
		{
			$this->status = $this->cancelStatus();
		}
		elseif ($this->allLinesReceived($linestatus)
				  || ($this->allLinesReceivedOrInvoiced($linestatus)
				  	  && !$this->allLinesInvoiced($linestatus)))
		{
			$this->status = $this->receivedStatus();
		}
		elseif ($this->allLinesInvoiced($linestatus))
		{
			$this->status = $this->invoiceStatus();
		}
		elseif ($this->allLinesAwaitingDelivery($linestatus)
				  && $this->status!=$this->acknowledgedStatus())
		{
			$this->status = $this->orderSentStatus();
		}
		elseif ($this->allLinesNew($linestatus))
		{
			$this->status = $this->newStatus();
		}

		$prev_net_value = $this->base_net_value;
		
		$po_line = DataObjectFactory::Factory('POrderLine');
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('order_id', '=', $this->id));
		$cc->add(new Constraint('status', '!=', $po_line->cancelStatus()));
		
		$totals = $po_line->getSumFields(
					array(
						'net_value',
						'twin_net_value',
						'base_net_value'
					),
					$cc,
					'po_lines'
			);
				
		unset($totals['numrows']);
		
		// set the correct totals back to the order header
		foreach($totals as $field=>$value)
		{
			$this->$field = bcadd($value,0);
		}

		// Check the authorisation limits on the order
		if ($this->type!='T' && $prev_net_value!=$this->base_net_value)
		{
			$porderlines_summary = new POrderLineCollection($po_line);
			$porderlines_summary->getAuthSummary($this->id);
			
			$this->authorised_by = $this->checkAuthLimits($porderlines_summary);
			
			if (!is_null($this->authorised_by) && EGS_USERNAME == $this->authorised_by && $this->base_net_value>0 && $porderlines_summary->count()>0)
			{
				$this->type='O';
				$this->date_authorised=date(DATE_FORMAT);
			}
			elseif ($this->type=='O')
			{
				$this->type			 = 'R';
				$this->authorised_by = $this->date_authorised = null;
				
				$awaitingauth = new POAwaitingAuthCollection();
				
				$awaitingauth->loadBy(null, $this->id);
				$awaitingauth->deleteAll();
				
				$result = $this->saveAuthList($porderlines_summary, $errors);
			}
		}
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$result=parent::save();
		
		if (!$result)
		{
			$errors[]='Error saving order header : '.$db->errorMsg();
			$db->FailTrans();
		}
		
		$db->CompleteTrans();
		
		return $result;
		
	}
	
	public function checkAuthLimits($porderlines_summary)
	{
// Check if the person saving the order has budget authorisation
// on the order value
// $authlimits is an array of [centre][account]=order value
		if ($porderlines_summary->count()>0)
		{
			foreach ($porderlines_summary as $summary)
			{
				$poauthlimit = DataObjectFactory::Factory('POAuthLimit', 'po_authlist');
				
				$cc = new ConstraintChain();
				
				$cc->add(new Constraint('glcentre_id', '=', $summary->glcentre_id));
				$cc->add(new Constraint('glaccount_id', '=', $summary->glaccount_id));
				$cc->add(new Constraint('username', '=', EGS_USERNAME));
				
				$poauthlimit->loadBy($cc);
				
				if (!$poauthlimit->isLoaded() || $poauthlimit->order_limit<$summary->net_value)
				{
					return null;
				}
			}
		}
		return EGS_USERNAME;
	}
	
	private function saveAuthList($porderlines_summary, &$errors)
	{
// Find the users that have budget authorisation on all Account/Centres
// $authlimits is an array of [centre][account]=order value
		if ($porderlines_summary->count()>0)
		{
			$authlist = array();
			
			$authcount = 0;
			
			foreach ($porderlines_summary as $summary)
			{
				$poauthlist = new POAuthLimitCollection();
				
				$poauthlist->getAuthList($summary->glaccount_id, $summary->glcentre_id, $summary->net_value);
				
				$authcount += 1;
				
				foreach ($poauthlist as $poauth)
				{
					if (isset($authlist[$poauth->username]))
					{
						$authlist[$poauth->username]+=1;
					}
					else
					{
						$authlist[$poauth->username]=1;
					}
				}
				
			}
			
// only interested in users that have authority across all account/centres
			if (count($authlist)>0)
			{
				foreach ($authlist as $username=>$count)
				{
					if ($authcount==$count)
					{
						$poawaitingauth = POAwaitingAuth::Factory(array('username'=>$username
																	   ,'order_id'=>$this->id)
																 ,$errors
																 ,'POAwaitingAuth');
						if (!$poawaitingauth || !$poawaitingauth->save())
						{
							$errors[] = 'Failed to save Order Awaiting Authorisation list';
							return false;
						}
					}
				}
			}
		}
		
		return true;
	}
	
	public function getGoodsReceivedNumbers()
	{
		
		$received_lines					= DataObjectFactory::Factory('POReceivedLine');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('order_id', '=', $this->id));
		
		return $received_lines->getAll($cc, true, true);
		
	}

	public function getInvoices()
	{
		$pi_lines = DataObjectFactory::Factory('PInvoiceLine');
		
		$pi_lines->idField			= 'invoice_id';
		$pi_lines->identifierField	= 'purchase_order_id';
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('purchase_order_id', '=', $this->id));
		
		return $pi_lines->getAll($cc);
		
	}
	
	public function getLineStatuses()
	{
		$porderline = DataObjectFactory::Factory('POrderLine');
		
		$porderlines = new POrderLineCollection($porderline);
		
		return parent::getLineStatuses($porderline, $porderlines);
	}
	
	public function getNextLineNumber ()
	{
		
		$porderline=DataObjectFactory::Factory('POrderLine');
		
		return parent::getNextLineNumber($porderline);
		
	}
	
	public function save_model($data)
	{
// Used to save Order Header and Order Lines from import or copy of existing
		$flash = Flash::Instance();

		if (empty($data['POrder']) || empty($data['POrderLine']))
		{
			$flash->addError('Error trying to import order');
			return false;
		}
		
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$header = $data['POrder'];

		$lines_data = DataObjectCollection::joinArray($data['POrderLine'], 0);
		
		if (!$lines_data || empty($lines_data))
		{
			$lines_data[] = $data['POrderLine'];
		}

		$order = POrder::Factory($header,$errors);
		
		if (!$order || count($errors)>0)
		{
			$errors[] = 'Order validation failed';
		}
		elseif (!$order->save()) {
			$errors[] = 'Order creation failed';
		}
		
		foreach ($lines_data as $line)
		{
			$line['order_id'] = $order->{$order->idField};
			
			$orderline = POrderLine::Factory($order, $line, $errors);
			
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
				$errors[] = 'Error updating Purchase Order totals';
			} else {
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

// end of POrder
