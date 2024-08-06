<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PoreceivedlinesController extends printController
{

	protected $version='$Revision: 1.42 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('POReceivedLine');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$s_data = array();

// Set context from calling module
		if (isset($this->_data['plmaster_id']))
		{
			$s_data['plmaster_id']=$this->_data['plmaster_id'];
		}

		if (isset($this->_data['status']))
		{
			$s_data['status']=$this->_data['status'];
		}

		if (isset($this->_data['order_number']))
		{
			$s_data['order_number']=$this->_data['order_number'];
		}

		if (!empty($this->_data['invoice_number']))
		{
			$s_data['invoice_number']=$this->_data['invoice_number'];
		}

		$this->setSearch('pogoodsreceivedSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'view');

		parent::index(new POReceivedLineCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				'confirm'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											 ,'action'=>'viewAwaitingDelivery'
											 ,'type'=>'confirm'
											 )
									   ),
					'tag'=>'Confirm Delivery'
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null)
	{
		$flash = Flash::Instance();

		parent::delete('POReceivedLine');

		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) :void
	{
		$flash=Flash::Instance();

		if(parent::save('POReceivedLine'))
		{
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
		}
		else
		{
			$this->refresh();
		}

	}

	public function view()
	{
// Load the orderline for the supplied id to get the order id
// Then get the order header and received orderlines for the order id
		$poreceivedline = DataObjectFactory::Factory('POReceivedline');

		if (isset($this->_data['id']))
		{
			$poreceivedline->load($this->_data['id']);
		}
		elseif (isset($this->_data['gr_number']))
		{
			$poreceivedline->loadBy('gr_number', $this->_data['gr_number']);
		}

		if (!$poreceivedline->isLoaded())
		{
			$flash=Flash::Instance();
			$flash->addError('Error getting Goods Received Note details');
			sendTo($this->name,'index',$this->_modules);
		}

		$porder = DataObjectFactory::Factory('POrder');
		$porder->load($poreceivedline->order_id);
		$this->view->set('grn', $poreceivedline);		
		$this->view->set('POrder', $porder);		

		$poreceivedlines = new POReceivedLineCollection(DataObjectFactory::Factory('POReceivedLine'));
		$poreceivedlines->getReceivedLines($poreceivedline->gr_number);
		$this->view->set('POReceivedlines', $poreceivedlines);
		$this->view->set('no_ordering', true);

		$can_cancel = true;

		foreach ($poreceivedlines as $poreceivedline)
		{
			if ($poreceivedline->isAccrued() || $poreceivedline->isCancelled() || $poreceivedline->isInvoiced() || !is_null($poreceivedline->invoice_number))
			{
				$can_cancel = false;
				break;
			}
		}

		$sidebar = new SidebarController($this->view);

		$actions = array();

		$actions['viewnotes']=array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'index'
								),
					'tag'=>'view goods received notes'
				);

		$actions['confirm']=array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'viewAwaitingDelivery'
								 ,'type'		=> 'confirm'
								 ),
					'tag'=>'Confirm Delivery'
				);

		$actions['print']=array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'print_label'
								 ,'gr_number'	=> $poreceivedline->gr_number
								 ),
					'tag'=>'print label'
				);

		if ($can_cancel)
		{
			$actions['cancel']=array(
						'link'=>array('modules'		=> $this->_modules
									 ,'controller'	=> $this->name
									 ,'action'		=> 'cancel_grn'
									 ,'gr_number'	=> $poreceivedline->gr_number
									 ),
						'tag'=>'Cancel GRN'
						);
		}

		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function cancel_grn()
	{

		$flash=Flash::Instance();

		$errors=array();

		if (!$this->CheckParams('gr_number'))
		{
			sendBack();
		}

		$poreceivedline = DataObjectFactory::Factory('POReceivedLine');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('gr_number', '=', $this->_data['gr_number']));

		$poreceivedlines = $poreceivedline->getAll($cc);

		$db=DB::Instance();
		$db->StartTrans();

		foreach ($poreceivedlines as $poreceivedline_id=>$value)
		{

			$poreceivedline = DataObjectFactory::Factory('POReceivedLine');
			$poreceivedline->load($poreceivedline_id);

			// Update the order line to reverse the GRN 
			$porderline = DataObjectFactory::Factory('POrderLine');
			$porderline->load($poreceivedline->orderline_id);

			if (!is_null($porderline->stitem_id))
			{
				$stitem = DataObjectFactory::Factory('STItem');
				$stitem->load($porderline->stitem_id);
				$qty_decimals=$stitem->qty_decimals;
			}
			else
			{
				$qty_decimals=maxdp($porderline->os_qty, $porderline->del_qty, $poreceivedline->received);
			}
			// get the received quantity for this orderline, excluding lines in the GRN
			$received_qty			= $poreceivedline->getReceivedQty($porderline->id, $poreceivedline->gr_number);
			$porderline->os_qty		= BCSUB($porderline->revised_qty, (string) $received_qty, $qty_decimals);
			$porderline->del_qty	= $received_qty;

			if ($porderline->del_qty > 0)
			{
				$porderline->status = $porderline->partReceivedStatus();
			}
			else
			{
				$porderline->status = $porderline->awaitingDeliveryStatus();
			}
			// Load the PO Header and save the orderline
			// to ensure header status is updated if required
			$porder = DataObjectFactory::Factory('POrder');
			$porder->load($porderline->order_id);
			if (!$porderline->save($porder))
			{
				$errors[] = 'Error updating PO line '.$db->ErrorMsg();
			}

			// Finally update the po received line
			$poreceivedline->status = $poreceivedline->cancelStatus();

			if (!$poreceivedline->save())
			{
				$errors[] = 'Error updating GRN '.$db->ErrorMsg();
			}
		}

		if (count($errors)===0)
		{

			// Now get the transactions for the GRN
			// Note each GRN line has two transactions linked by transfer_id
			$sttransaction = DataObjectFactory::Factory('STTransaction');
			$sttransaction->identifierField='transfer_id';

			$cc = new ConstraintChain();

			$cc->add(new Constraint('process_name', '=', 'GR'));
			$cc->add(new Constraint('process_id', '=', $this->_data['gr_number']));

			$sttransactions = $sttransaction->getAll($cc);

			// Need to link new transfer ids to existing transactions transfer_ids
			$transfer_ids = array();
			$transferrule = DataObjectFactory::Factory('WHTransferrule');

			foreach ($sttransactions as $sttransaction_id=>$value)
			{
				if (!isset($transfer_ids[$value]))
				{
					$transfer_ids[$value] = $transferrule->getTransferId()->transfer_id;
				}
			}

			// Reverse each of the transaction pairs associated with the GRN lines
			foreach ($sttransactions as $sttransaction_id=>$value)
			{

				$sttransaction = DataObjectFactory::Factory('STTransaction');
				$sttransaction->load($sttransaction_id);

				// create new transaction by setting new id value
				$test = $sttransaction->autoHandle($sttransaction->idField);

				if($test!==false)
				{
					$sttransaction->{$sttransaction->idField}=$test;
				}
				else
				{
					$errors[] = 'Error getting identifier for new item';
				}

				// Reverse the quantity and save
				$sttransaction->transfer_id	= $transfer_ids[$sttransaction->transfer_id];
				$sttransaction->created		= $sttransaction->autoHandle('created');
				$sttransaction->createdby	= EGS_USERNAME;
				$sttransaction->qty			= $sttransaction->qty*-1;

				$sttransaction->save($errors);

			}
		}

		// Check for errors
		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$flash->addError('Error cancelling GRN '.$this->_data['gr_number']);
			$db->FailTrans();
		}
		else
		{
			$flash->addMessage('GRN '.$this->_data['gr_number'].' Cancelled');
		}
		$db->CompleteTrans();

		sendTo($this->name, 'index', $this->_modules);

	}

	public function confirm_receipt()
	{

		if (!$this->CheckParams('POrderLine'))
		{
			sendBack();
		}

		$flash=Flash::Instance();
		$db = DB::Instance();
		$errors=array();

		$generator = new GoodsReceivedNumberHandler();
		$gr_note = $generator->handle(DataObjectFactory::Factory('POReceivedLine'));
		$data['gr_number'] = $gr_note;

		$lines_data = $this->_data['POrderLine'];

		$confirm_count=0;

		foreach ($lines_data as $id=>$line)
		{
			if (isset($line['confirm']))
			{
				$confirm_count++;
				$db->StartTrans();

				$orderline = DataObjectFactory::Factory('POrderLine');
				$orderline->load($id);

				$del_qty = $line['received_qty'];

				$data = array();

				$data['id']						= $orderline->id;
				$data['os_qty']					= $orderline->os_qty-$del_qty;
				$data['del_qty']				= $orderline->del_qty + $del_qty;
				$data['actual_delivery_date']	= date(DATE_FORMAT);
				// Required for optimistic locking
				$data['lastupdated']			= $line['lastupdated'];

				if ($data['os_qty']<=0)
				{
					$data['status'] = 'R';
					$data['os_qty'] = 0;
				}
				else
				{
					$data['status'] = 'P';
				}

				$updatedline=DataObject::Factory($data, $errors, 'POrderLine');

				if (count($errors)==0 && $updatedline)
				{
					$result = $updatedline->save();
				}
				else
				{
					$result = false;
				}

				if ($result)
				{
					$porder = DataObjectFactory::Factory('POrder');

					$porder->load($updatedline->order_id);

					$linestatuses = array();

					// Test that the order is loaded and is not a requisition
					if ($porder->isLoaded() && $porder->type == 'O')
					{
						$linestatuses = $porder->getLineStatuses();

						if ($porder->allLinesReceived($linestatuses))
						{
							$porder->status = 'R';
						}
						elseif (!$porder->allLinesReceivedOrInvoiced($linestatuses))
						{
							$porder->status = 'P';
						}

						$result = $porder->save();

					}
					else
					{
						$result=false;
					}
				}

				$data = array();

				$porder = DataObjectFactory::Factory('POrder');
				$porder->load($orderline->order_id);

				$data['gr_number']			= $gr_note;
				$data['order_id']			= $orderline->order_id;
				$data['plmaster_id']		= $porder->plmaster_id;
				$data['received_date']		= date(DATE_FORMAT);
				$data['received_qty']		= $del_qty;
				$data['currency']			= $orderline->currency;
				$data['net_value']			= bcmul((string) $del_qty, $orderline->price);
				$data['orderline_id']		= $orderline->id;
				$data['stuom_id']			= $orderline->stuom_id;
				$data['stitem_id']			= $orderline->stitem_id;
				$data['productline_id']		= $orderline->productline_id;
				$data['item_description']	= $orderline->item_description;
				$data['tax_rate_id']		= $orderline->tax_rate_id;
				$data['status']				= 'R';
				$data['delivery_note']		= $this->_data['delivery_note'];
				$data['received_by']		= $line['received_by'];

				$stitem = DataObjectFactory::Factory('STItem');
				$stitem->load($data['stitem_id']);

				$param = DataObjectFactory::Factory('GLParams');

				$net_mass_uom_id = $param->intrastat_net_mass();

				if ($stitem->isLoaded() && !empty($net_mass_uom_id))
				{
					$data['net_mass'] = $stitem->convertToUoM($data['stuom_id'], $net_mass_uom_id, $data['received_qty']);
				}

				if (empty($data['net_mass']) || $data['net_mass']===false)
				{
					$data['net_mass'] = 0;
				}

				$receivedline = POReceivedLine::Factory($data, $errors, 'POReceivedLine');

				if (count($errors)>0)
				{
					$flash->addErrors($errors); 
				}

				if (!$result || !$receivedline || !$receivedline->save())
				{
					$flash->addError('Error creating Goods Received Note '.$gr_note);
					$db->FailTrans();
					$db->CompleteTrans();
					sendBack();
				}

				if (is_null($orderline->stitem_id))
				{
					// Not stock item so no transactions to create, just do commit
					$db->CompleteTrans();
				}
				else
				{
					if (!empty($line['whaction_id']))
					{
					// Create transaction pair for Goods Received
					// if the receipt is for a stock item
						$data = array();

						$data['process_name']		= 'GR';
						$data['process_id']			= $gr_note;
						$data['whaction_id']		= $line['whaction_id'];
						$data['from_whlocation_id']	= $line['from_whlocation_id'];
						$data['to_whlocation_id']	= $line['to_whlocation_id'];
						$data['to_whbin_id']		= $line['to_whbin_id'];
						$data['stitem_id']			= $orderline->stitem_id;
						$data['qty']				= $del_qty;

						if (!empty($data['stitem_id']))
						{
							$stitem = DataObjectFactory::Factory('STItem');
							$stitem->load($data['stitem_id']);
							if ($stitem)
							{
								$data['qty'] = round($stitem->convertToUoM($orderline->stuom_id, $stitem->uom_id, $del_qty),$stitem->qty_decimals);
							}
						}

						if ($del_qty<=0)
						{
							$errors[] = 'Delivered quantity must be greater than zero';
						}

						$models=STTransaction::prepareMove($data, $errors);

						$result=false;

						if (count($errors)===0)
						{
							foreach ($models as $model)
							{
								$result=$model->save($errors);
								if($result===false)
								{
									break;
								}
							}
						}

						if (count($errors)>0 || !$result)
						{
							$flash->addErrors($errors);
							$flash->addError('Error updating stock');
							$db->FailTrans();
							$db->CompleteTrans();
							$this->refresh();
						}
					}

					$db->CompleteTrans();

// Need to commit here so that any errors found in backflush
// can be written as transaction errors					
					$stitem = DataObjectFactory::Factory('STItem');
					$stitem->load($orderline->stitem_id);

					$data['book_qty'] = $del_qty;

					MFStructure::backflush ($data, $stitem->getChildStructures(), $errors);
				}			
			}
		}

		if ($confirm_count==0)
		{
			$errors[]='No Goods Recieved Notes Selected';
		}

		if (count($errors)===0)
		{
			$flash->addWarning('GRN '.$gr_note.' created OK');
			$flash->addMessage('Goods Received confirmed OK');

			if (isset($this->_data['saveAnother']))
			{
				sendTo($this->name, 'viewAwaitingDelivery', $this->_modules);
			}

			if (isset($this->_data['savePrintLabels']))
			{
				sendTo($this->name, 'print_label', $this->_modules, array('gr_number'=>$gr_note));
			}

			sendTo($this->name,'index',$this->_modules);
		}
		else
		{
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	public function save_grnote()
	{

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$gr_note = $this->_data['gr_note'];

		$errors = array();

		$received = array();

		$receivedline = array();

		foreach ($this->_data['poreceivedlines'] as $key=>$value)
		{
			$orderline = DataObjectFactory::Factory('POrderLine');
			$orderline->load($key);

			if ($orderline)
			{
				$order = DataObjectFactory::Factory('SOrder');
				$order->load($orderline->order_id);
				if ($order)
				{
					$receivedline['order_id']			= $order->id;
					$receivedline['orderline_id']		= $orderline->id;
					$receivedline['plmaster_id']		= $order->plmaster_id;
					$receivedline['stuom_id']			= $orderline->stuom_id;
					$receivedline['item_description']	= $orderline->description;
					$receivedline['tax_rate_id']		= $orderline->tax_rate_id;

					if ($orderline->stitem_id)
					{
						$receivedline['stitem_id'] = $orderline->stitem_id;
					}

					if ($orderline->productline_id)
					{
						$receivedline['productline_id'] = $orderline->productline_id;
					}

					$receivedline['received_qty']	= $orderline->os_qty;
					$receivedline['received_date']	= date(DATE_FORMAT);
					$receivedline['status']			= 'N';
					$received[$order->id][]			= $receivedline;
				}
			}
		}

		foreach ($received as $header)
		{
			foreach ($header as $line)
			{
				$line['gr_note']=$gr_note;

				$saveline = POReceivedLine::Factory($line,$errors,'POReceivedLine');

				if ($saveline)
				{
					$result = $saveline->save();

					if(!$result)
					{
						$flash->addError('Error creating goods received note');

						$db->FailTrans();

						sendBack();
					}
				}

				$orderline = DataObjectFactory::Factory('POrderLine');

				$result = $orderline->update($line['orderline_id'],'gr_note',$gr_note);

				if($result===false)
				{
					$flash->addError('Error updating order line');

					$db->FailTrans();

					sendBack();
				}
			}
		}

		if (count($errors)===0 && $db->CompleteTrans())
		{
			$flash->addMessage('Goods Received Notes added successfully');

			sendTo($this->name,'index',$this->_modules);
		}
		else
		{
			$flash->addErrors($errors);

			$db->FailTrans();

			sendBack();
		}

	}

	public function print_label()
	{
		$poreceivedline = DataObjectFactory::Factory('POReceivedline');

		if (isset($this->_data['id']))
		{
			$poreceivedline->load($this->_data['id']);
		}
		elseif (isset($this->_data['gr_number']))
		{
			$poreceivedline->loadBy('gr_number', $this->_data['gr_number']);
		}

		if (!$poreceivedline->isLoaded())
		{
			$flash=Flash::Instance();
			$flash->addError('Error getting Goods Received Note details');
			sendTo($this->name,'index',$this->_modules);
		}

		$porder = DataObjectFactory::Factory('POrder');
		$porder->load($poreceivedline->order_id);
		$this->view->set('grn', $poreceivedline);
		$this->view->set('POrder', $porder);

		$poreceivedlines = new POReceivedLineCollection();
		$poreceivedlines->getReceivedLines($poreceivedline->gr_number);

		$this->view->set('POReceivedlines', $poreceivedlines);

		$this->view->set('printers', $this::selectPrinters());
		$this->view->set('default_printer', $this->getDefaultPrinter());

		$sidebar = new SidebarController($this->view);

		$actions = array();

		$actions['viewnotes']=array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'index'
								),
					'tag'=>'view goods received notes'
				);

		$actions['confirm']=array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'viewAwaitingDelivery'
								 ,'type'		=> 'confirm'
								 ),
					'tag'=>'Confirm Delivery'
				);

		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function confirm_print_labels()
	{
		$flash = Flash::Instance();

		$errors=array();

		if (!$this->checkParams('POReceivedLine'))
		{
			$this->dataError();
			sendBack();
		}

		// May want to move this to a separate class (See Manufacturing Works Orders Reports)
		// to allow for custom overload
		$data['printtype']		= 'pdf';
		$data['printaction']	= 'Print';
		$data['printer']		= $this->_data['printer'];
//		$data['attributes']['orientation-requested'] = 'landscape';
		$data['attributes']['media'] = 'A5';

		$label_types = array('pallet', 'item');

		foreach ($this->_data['POReceivedLine'] as $key=>$line)
		{
			if (isset($line['print']))
			{
				$item_type = explode('-', (string) $line['stitem']);

				foreach ($label_types as $label_type)
				{
					if ($line[$label_type.'_count'] > 0)
					{
						if (isset($line[$label_type.'_labels']))
						{
							$line[$label_type.'_count'] *= $line[$label_type.'_labels'];
						}

						for ($i=0; $i<$line[$label_type.'_count']; $i++)
						{
							$extra[]['GRN'] = array('item_type'	=> $item_type[0]
											,'item_code'		=> trim($item_type[1])
											,'gr_number'		=> $line['gr_number']
											,'received_qty'		=> number_format($line[$label_type.'_qty'], $line['qty_decimals'])
											,'received_date'	=> un_fix_date($line['received_date']));
						}
					}
				}
			}
		}

		// generate the XML, include the extras array too
		$xml = $this->generateXML(array('extra'=>$extra));

		// build a basic list of options
		$options = array(
			'report'	=> 'GRN-labels',
			'xmlSource'	=> $xml
		);

		$response = json_decode((string) $this->generate_output($data,$options));

		if($response->status!==true) {
			$flash->addError($options['report'].": ".$response->message);
		} else {
			$flash->addMessage($options['report']." printed successfully");
		}
		sendBack();

		if (count($errors)>0) {
			$flash->addErrors($errors);
		} else {
			$flash->addMessage('Print Works Order Paperwork Completed');
		}
	}

	public function viewAwaitingDelivery()
	{

		$s_data = array();

// Set context from calling module

		if (isset($this->_data['plmaster_id']))
		{
			$s_data['plmaster_id'] = $this->_data['plmaster_id'];
		}

		if (isset($this->_data['stitem_id']))
		{
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}

		if (isset($this->_data['order_id']))
		{
			$s_data['order_id'] = $this->_data['order_id'];
		}

		$this->setSearch('pogoodsreceivedSearch', 'confirmReceipt', $s_data);

		$s_data['plmaster_id']	= $this->search->getValue('plmaster_id');
		$s_data['stitem_id']	= $this->search->getValue('stitem_id');
		$s_data['order_id']		= $this->search->getValue('order_id');

		$orderlines = new POrderLineCollection(DataObjectFactory::Factory('POrderLine'));

		if ($s_data['plmaster_id']>0
		|| $s_data['stitem_id']>0
		|| $s_data['order_id']>0)
		{
			if (isset($this->_data['orderby']))
			{
				$sh = new SearchHandler($orderlines,true);
			}
			else
			{
				$sh = new SearchHandler($orderlines,false);
			}

			$sh->extract();
			$sh->setLimit('');

			$sh->addConstraint(new Constraint('status', 'in', "('A','P')"));

			if ($s_data['plmaster_id']>0)
			{
				$sh->addConstraint(new Constraint('plmaster_id', '=', $s_data['plmaster_id']));
			}

			if ($s_data['stitem_id']>0)
			{
				$sh->addConstraint(new Constraint('stitem_id', '=', $s_data['stitem_id']));
			}

			if ($s_data['order_id']>0)
			{
				$sh->addConstraint(new Constraint('order_id', '=', $s_data['order_id']));
			}

			$DisplayFields=array('id');

			foreach ($orderlines->getFields() as $field=>$attr)
			{
				if (!$attr->ignoreField)
				{
					$DisplayFields[] = $field;
				}
			}

			$DisplayFields[] = 'receive_action';

			$sh->setFields($DisplayFields);

			$orderlines->load($sh);

			$this->view->set('num_records', $orderlines->num_records);

			foreach ($orderlines as $orderline)
			{
				$orderline->getAction();

				$orderline->whaction_id			= $orderline->receive_action;
				$orderline->from_location_id	= $orderline->getFromLocation($orderline->receive_action);
				$orderline->to_location_id		= $orderline->getToLocation($orderline->receive_action);

				$whlocation = DataObjectFactory::Factory('WHLocation');
				$whlocation->load($orderline->to_location_id);

				if ($whlocation->isLoaded())
				{
					$orderline->to_location = $whlocation->whstore.'/'.$whlocation->description;
				}
				else
				{
					$orderline->to_location = '';
				}

				$orderline->to_bin_list = $orderline->getToBin($orderline->receive_action);
			}
		}

		$this->_templateName = $this->getTemplateName('confirmreceipt');

		$this->view->set('porderlines',$orderlines);

		$sidebar = new SidebarController($this->view);

		$actions = array();

		$actions['viewnotes'] = array(
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'index'
								 ),
					'tag'=>'view goods received notes'
				);			

		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->view->set('page_title',$this->getPageName('Confirm Delivery of Order',''));
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		// Override output button - there is no point to it here
		$this->printaction = '';

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName(($base)?$base:'goods_received_note',$action);
	}

}

// End of PoreceivedlinesController
