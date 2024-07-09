<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class STTransaction extends DataObject
{

	protected $version = '$Revision: 1.15 $';
	
	protected $defaultDisplayFields = array('stitem'=>'stock_item'
											,'created'
											,'process_name'
											,'process_id'
											,'flocation'=>'from_location'
											,'fbin'=>'from_bin'
											,'whlocation'=>'to_location'
											,'whbin'=>'to_bin'
											,'qty'
											,'error_qty'
											,'balance'
											,'status'
											,'remarks');

	function __construct($tablename = 'st_transactions')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		$this->idField = 'id';
		
		// Set specific characteristics
		$this->orderby = array('created', 'id');
		$this->orderdir = array('DESC', 'DESC');
		
		// Define relationships
		$st_filter = new ConstraintChain();
        $st_filter->add(new Constraint('obsolete_date', 'is', 'NULL'));
		$st_filter->add(new Constraint('comp_class', '!=', 'P'));

		$this->belongsTo('STItem', 'stitem_id', 'stitem', $st_filter); 
		$this->belongsTo('WHLocation', 'whlocation_id', 'whlocation'); 
		$this->belongsTo('WHBin', 'whbin_id', 'whbin'); 
		$this->belongsTo('GLAccount', 'glaccount_id', 'account'); 
		$this->belongsTo('GLCentre', 'glcentre_id', 'cost_centre'); 
		$this->belongsTo('WHAction', 'whaction_id', 'process_action'); 
		
		// Define field formats
		
		// set formatters, more set in load() function

		// Define enumerated types
		$this->setEnum('status', array('O'=>'OK'
									  ,'E'=>'Error'
									  ,'R'=>'Resolved'
									  ,'C'=>'Cancelled'
									  )
				);
		
		$this->setEnum('process_name', array('D'=>'Despatch'
											,'EN'=>'Engineering Job'
											,'GR'=>'Goods Received'
											,'KP'=>'Kit Production'
											,'SC'=>'Sales Credit'
											,'SI'=>'Sales Invoice'
											,'SO'=>'Sales Order'
											,'WO'=>'Works Order'
											)
				);
									
		// Define default values
		$this->getField('status')->setDefault('O');

	}

	public function getFKvalue()
	{
		switch ($this->process_name)
		{
			case 'D':
				$value = $this->process_id;
				$link = array('module'			=> 'despatch'
							, 'controller'		=> 'sodespatchlines'
							, 'action'			=> 'view'
							, 'despatch_number'	=> $this->process_id);
				break;
			case 'GR':
				$value = $this->process_id;
				$link = array('module'		=> 'goodsreceived'
							, 'controller'	=> 'poreceivedlines'
							, 'action'		=> 'view'
							, 'gr_number'	=> $this->process_id);
				break;
			case 'SC':
			case 'SI':
				$model = DataObjectFactory::Factory('SInvoice');
				$model->load($this->process_id);
				$value = ($model->isLoaded())?$model->getIdentifierValue():'';
				$link = array('module'		=> 'sales_invoicing'
							, 'controller'	=> 'sinvoices'
							, 'action'		=> 'view'
							, 'id'			=> $this->process_id);
				break;
			case 'SO':
				$model = DataObjectFactory::Factory('SOrder');
				$model->load($this->process_id);
				$value = ($model->isLoaded())?$model->getIdentifierValue():'';
				$link = array('module'		=> 'sales_order'
							, 'controller'	=> 'sorders'
							, 'action'		=> 'view'
							, 'id'			=> $this->process_id);
				break;
			case 'WO':
				$model = DataObjectFactory::Factory('MFWorkorder');
				$model->load($this->process_id);
				$value = ($model->isLoaded())?$model->getIdentifierValue():'';
				$link = array('module'		=> 'manufacturing'
							, 'controller'	=> 'mfworkorders'
							, 'action'		=> 'view'
							, 'id'			=> $this->process_id);
				break;
			case 'KP':
				$model = DataObjectFactory::Factory('STItem');
				$model->load($this->process_id);
				$value = ($model->isLoaded())?$model->getIdentifierValue():'';
				$link = array('module'		=> 'manufacturing'
							, 'controller'	=> 'stitems'
							, 'action'		=> 'view'
							, 'id'			=> $this->process_id);
				break;
			default:
				$value = '';
		}
		
		return array('value'=>$value, 'link'=>$link);
		
	}
	
	static function getTransferLocations(&$data, &$errors = [])
	{
		if (!isset($data['whaction_id']) || empty($data['whaction_id']))
		{
			$errors[] = 'Transfer Action is missing';
			return false;
		}
		
		$rules = new WHTransferruleCollection();
		
		$sh = new SearchHandler($rules,false);
		
		$sh->addConstraint(new Constraint('whaction_id', '=', $data['whaction_id']));
		
		$rules->load($sh);
		
		if ($rules->count() > 1)
		{
		// Multiple transfer rules are not supported; this would require to display a
		//  drop down list of available transfer rules so the required rule can be selected 
			$errors[] = 'Multiple transfer rules not allowed here';
			return false;
		}
		
		foreach ($rules as $rule)
		{
			$transfer_from	= $rule->from_whlocation_id;
			$transfer_to	= $rule->to_whlocation_id;
		}
					
		$data['from_whlocation_id']	= $transfer_from;
		$data['to_whlocation_id']	= $transfer_to;

		return true;
	}
	
	static function prepareMove($data, &$errors = [])
	{

		$item = DataObjectFactory::Factory('STItem');
		
		$item->load($data['stitem_id']);
		
		$copyfields = array('std_cost',
							'std_mat',
							'std_lab',
							'std_osc',
							'std_ohd',
							'latest_cost',
							'latest_mat',
							'latest_lab',
							'latest_osc',
							'latest_ohd');
		
 		$from	= array();
 		$to		= array();

		$from['stitem_id'] = $to['stitem_id'] = $data['stitem_id'];

// Link the pair of transaction through the transfer_id
		$transferrule = DataObjectFactory::Factory('WHTransferrule');
		
		$transfer_id = $transferrule->getTransferId();
		
		$from['transfer_id'] = $to['transfer_id'] = $transfer_id->transfer_id;

// Identify the Action and Process that caused this transaction
		$from['whaction_id'] = $to['whaction_id'] = $data['whaction_id'];
		
		if (isset($data['process_name']))
		{
			$from['process_name'] = $to['process_name'] = $data['process_name'];
		}
		
		if (isset($data['process_id']))
		{
			$from['process_id'] = $to['process_id'] = $data['process_id'];
		}

// Now create the pair of transaction records from the input data
		foreach ($copyfields as $field)
		{
			$to[$field]		= $item->$field;
			$from[$field]	= $item->$field;
		}

		$from['qty']	=-$data['qty'];
		$to['qty']		= $data['qty'];
		
		if (isset($data['remarks']))
		{
			$from['remarks'] = $to['remarks'] = $data['remarks'];
		}
		
		if (isset($data['status'])) {
			$from['status'] = $to['status'] = $data['status'];
		}
		
		$loctypes = array('whlocation_id','whbin_id');

		foreach ($loctypes as $type)
		{
			if (isset($data['from_'.$type]))
			{
				$from[$type] = $data['from_'.$type];
			}
			
			if (isset($data['to_'.$type]))
			{
				$to[$type] = $data['to_'.$type];
			}
		}

		if (isset($data['from_whlocation_id']))
		{
			$whlocation = DataObjectFactory::Factory('WHLocation');
			
			$whlocation->load($data['from_whlocation_id']);
			
			if ($whlocation)
			{
				$from['glaccount_id']	= $whlocation->glaccount_id;
				
				$from['glcentre_id']	= $whlocation->glcentre_id;
				
				if (!empty($data['from_whbin_id']) && !WHBin::validBinLocation($data['from_whbin_id'],$data['from_whlocation_id']))
				{
					$errors[] = 'From Bin invalid for this location';
				}
			}
		}
		if (isset($data['to_whlocation_id']))
		{
			$whlocation = DataObjectFactory::Factory('WHLocation');
			
			$whlocation->load($data['to_whlocation_id']);
			
			if ($whlocation)
			{
				$to['glaccount_id']	= $whlocation->glaccount_id;
				
				$to['glcentre_id']	= $whlocation->glcentre_id;
				
				if (!empty($data['to_whbin_id']) && !WHBin::validBinLocation($data['to_whbin_id'],$data['to_whlocation_id']))
				{
					$errors[] = 'To Bin invalid for this location';
				}
			}
		}
		
// Set the balance to zero; the balance will be updated in the save
		$from['balance'] = $to['balance'] = 0;
		
// Prepare the two transaction records for saving
		$from_model	= DataObject::Factory($from, $errors, 'STTransaction');
		$to_model	= DataObject::Factory($to, $errors, 'STTransaction');

		return array('from'=>$from_model, 'to'=>$to_model);

	}

	public function save(&$errors)
	{
// Save the transaction record
// and insert/balances associated with the location

		$location = DataObjectFactory::Factory('WHLocation');
		
		$location->load($this->whlocation_id);

		if ( $location->isBalanceEnabled() )
		{
			$stbalance = DataObjectFactory::Factory('STBalance');
			$cc = new ConstraintChain();    //then we start a chain

			$cc->add(new Constraint('stitem_id', '=', $this->stitem_id));

			$cc->add(new Constraint('whlocation_id', '='
								   ,$this->whlocation_id));
			
			if ($this->whbin_id)
			{
				$cc->add(new Constraint('whbin_id', '=', $this->whbin_id));
			}

			$result = $stbalance->loadBy($cc);    //and then do the load 

			$data = array();
			
			$data['stitem_id'] = $this->stitem_id;
			
			$data['whlocation_id'] = $this->whlocation_id;
			
			if ($location->isBinControlled() && $this->whbin_id=='')
			{
				$errors[] = 'No Bin Selected for location '.$location->getIdentifierValue();
				return false;
			}
			else
			{
				$data['whbin_id'] = $this->whbin_id;
			}
			
			$data['balance'] = $this->qty;
			$stitem = DataObjectFactory::Factory('STItem');
			
			$stitem->load($this->stitem_id);
			
			if($result !== false && $stitem)
			{
				$data['id']		= $stbalance->id;
				$data['balance']= bcadd($data['balance'], $stbalance->balance, $stitem->qty_decimals);
			}
						
			$this->balance = $data['balance'];
			
			$stbalance = DataObject::Factory($data, $errors, 'STBalance');
			
			if (count($errors)==0)
			{
				$stbalance->save($errors);
			}
			
			if (count($errors) > 0)
			{
				return false;
			}
			elseif ($stitem)
			{
				$stitem->balance = bcadd($stitem->balance, $this->qty, $stitem->qty_decimals);
				$stitem->save();
			}
		}
		
		return parent::save();
	}
	
	public function getTwinTransaction()
	{
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('transfer_id', '=', $this->transfer_id));
		
		$cc->add(new Constraint('id', '!=', $this->id));
		
		$sttransaction = DataObjectFactory::Factory('STTransaction');
		
		if (!$sttransaction->loadBy($cc))
		{
			return null;
		}
		
		return $sttransaction;
	}
	
	public function current_balance()
	{
		$cc = new ConstraintChain;
		
		$cc->add(new Constraint('stitem_id', '=', $this->stitem_id));
		
		$cc->add(new Constraint('whlocation_id', '=', $this->whlocation_id));
		
		if (!is_null($this->whbin_id))
		{
			$cc->add(new Constraint('whbin_id', '=', $this->whbin_id));
		}
		
		$stbalance = DataObjectFactory::Factory('STBalance');
		
		if (!$stbalance->loadBy($cc))
		{
			return 0;
		}
		
		return $stbalance->balance;
	}
	
	public function from_location()
	{
		$sttransaction = $this->getTwinTransaction();
		
		if (!$sttransaction)
		{
			return null;
		}
		
		return $sttransaction->whlocation;
	}
	
	public function from_bin()
	{
		$sttransaction = $this->getTwinTransaction();
		
		if (!$sttransaction)
		{
			return null;
		}
		
		return $sttransaction->whbin;
	}

	public function positive_qty()
	{
		return abs($this->qty);
	}
	
	public function positive_error_qty()
	{
		if (is_null($this->error_qty))
		{
			return null;
		}
		
		return abs($this->error_qty);
	}

	public function resolved_balance()
	{
		return ($this->current_balance() - $this->positive_error_qty());
	}
	
	public function getUoM ($stitem_id='')
	{
		
		$stitem = DataObjectFactory::Factory('STItem');
		
		if ($this->isLoaded() && empty($stitem_id))
		{
			$stitem_id=$this->stitem_id;
		}
		
		if (empty($stitem_id))
		{
			return '';
		}
		else
		{
			$stitem->load($stitem_id);
			
			return $stitem->uom_name;
		}
		
	}
	
}

// End of STTransaction
