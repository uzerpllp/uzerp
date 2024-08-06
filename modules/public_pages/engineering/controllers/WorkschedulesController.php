<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WorkschedulesController extends printController
{

	protected $version='$Revision: 1.5 $';

	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{

		parent::__construct($module, $action);

		$this->uses(DataObjectFactory::Factory('EngineeringResource'), false);

		$this->_templateobject = DataObjectFactory::Factory('WorkSchedule');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'view');

		$s_data = null;

		$flash = Flash::Instance();

		$errors = array();

		if(isset($this->_data['Search']))
		{
			$s_data = $this->_data['Search'];
		}

		$this->setSearch('EngineeringSearch', 'workSchedules', $s_data);

		if(count($errors)>0)
		{
			$flash->addErrors($errors);
			$this->search->clear();
		}

		parent::index(new WorkScheduleCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array(
				'new'	=> array('tag'	=> 'new_work_schedule'
								,'link'	=> array('modules'		=> $this->_modules
												,'controller'	=> $this->name
												,'action'		=> 'new')
				)
			)
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('no_delete',true);
	}

	public function update_status()
	{

		if (!$this->loadData() || !$this->checkParams('status'))
		{
			$this->dataError();
			sendBack();
		}

		$workschedule = $this->_uses[$this->modeltype];

		$flash = Flash::Instance();

		if (!$workschedule->update($workschedule->id, 'status', $this->_data['status']))
		{
			$flash->addError('Failed to update Work Schedule');
		}
		else
		{
			$flash->addMessage('Work Schedule '.$workschedule->job_no.' now '.$workschedule->getEnum('status', $this->_data['status']));
		}

		sendBack();

	}

	public function delete($modelName = null)
	{

		$flash = Flash::Instance();

		$flash->addError('Deleting Work Schedules is not allowed');

		sendBack();

	}

	public function _new()
	{

		parent::_new();

		$resource = DataObjectFactory::Factory('MFResource');
		$this->view->set('resource', $resource->getAll());

		$workschedule = $this->_uses[$this->modeltype];

		if ($workschedule->isLoaded())
		{
			$eng_resource	= DataObjectFactory::Factory('EngineeringResource');
			$this->view->set('assigned', $eng_resource->getAssigned($workschedule->id));
		}

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		$flash = Flash::Instance();
		$errors = array();

		if(parent::save($this->modeltype, $this->_data['WorkSchedule'], $errors))
		{
			$idField = $this->saved_model->idField;

		}

		if (count($errors) > 0)
		{
			$flash->addErrors($errors);
			$this->refresh();
		}
		else
		{
			sendTo($this->name, 'view', $this->_modules, array($idField=>$this->saved_model->{$idField}));
		}

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$workschedule = $this->_uses[$this->modeltype];

		$id_field	= $workschedule->idField;
		$id_value	= $workschedule->{$workschedule->idField};

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll']	= array('tag'	=> 'View'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'index'
														)
									  );

		$sidebarlist['new']	= array('tag'	=> 'New Work Schedule'
								   ,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'new'
													)
									   );

		$sidebar->addList('All Work Schedules',$sidebarlist);

		$sidebarlist=array();

		$sidebarlist['view']	= array('tag'	=> 'View'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'view'
														,$id_field		=> $id_value
														)
									  );

		$sidebarlist['edit']	= array('tag'	=> 'Edit'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'edit'
														,$id_field		=> $id_value
														)
									   );

		if ($workschedule->isNew())
		{
			$sidebarlist['activate']	= array('tag'	=> 'Activate'
											   ,'link'	=> array('modules'		=> $this->_modules
																,'controller'	=> $this->name
																,'action'		=> 'update_status'
																,$id_field		=> $id_value
																,'status'		=> $workschedule->activeStatus()
														)
			);

			$sidebarlist['cancel']	= array('tag'	=> 'Cancel'
										   ,'link'	=> array('modules'		=> $this->_modules
															,'controller'	=> $this->name
															,'action'		=> 'update_status'
															,$id_field		=> $id_value
															,'status'		=> $workschedule->cancelledStatus()
															)
			);
		}
		elseif ($workschedule->isActive())
		{
			$sidebarlist['complete']	= array('tag'	=> 'Complete'
											   ,'link'	=> array('modules'		=> $this->_modules
																,'controller'	=> $this->name
																,'action'		=> 'update_status'
																,$id_field		=> $id_value
																,'status'		=> $workschedule->completedStatus()
																)
			);
		}

		$sidebar->addList('This Work Schedule',$sidebarlist);

		$this->sidebarRelatedItems($sidebar, $workschedule);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function view_parts()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$workschedule = $this->_uses[$this->modeltype];

		$eng_resources = new WorkSchedulePartCollection();

		$sh	= $this->setSearchHandler($eng_resources);

		$sh->addConstraint(new Constraint('work_schedule_id', '=', $workschedule->{$workschedule->idField}));

		parent::index($eng_resources, $sh);

//		$this->view->set('resources', $eng_resources);

	}

	public function view_notes()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$workschedule = $this->_uses[$this->modeltype];

		$notes	= new WorkScheduleNoteCollection();

		$sh	= $this->setSearchHandler($notes);

		$sh->addConstraint(new Constraint('work_schedule_id', '=', $workschedule->{$workschedule->idField}));

		parent::index($notes, $sh);

		$this->view->set('clickaction', 'edit');
		$this->view->set('clickcontroller', 'workschedulenotes');
		$this->view->set('collection',$notes);

	}

	public function view_resources()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$workschedule = $this->_uses[$this->modeltype];

		$eng_resources	= new EngineeringResourceCollection();

		$sh	= $this->setSearchHandler($eng_resources);

		$sh->addConstraint(new Constraint('work_schedule_id', '=', $workschedule->{$workschedule->idField}));

		parent::index($eng_resources, $sh);

		$this->view->set('clickaction', 'edit');
		$this->view->set('clickcontroller', 'engineeringresources');

	}

	public function view_transactions ()
	{
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$works_order = $this->_uses[$this->modeltype];

		$transaction = DataObjectFactory::Factory('STTransaction');
		$transaction->setDefaultDisplayFields(array('stitem'=>'stock_item'
													,'created'
													,'flocation'=>'from_location'
													,'fbin'=>'from_bin'
													,'whlocation'=>'to_location'
													,'whbin'=>'to_bin'
													,'qty'
													,'error_qty'
													,'balance'
													,'status'
													,'remarks'));

		$related_collection = new STTransactionCollection($transaction);

		$sh = $this->setSearchHandler($related_collection);
		$sh->addConstraint(new Constraint('process_id', '=', $works_order->id));
		$sh->addConstraint(new Constraint('process_name', '=', $this->_templateobject->transaction_type()));
		$sh->addConstraint(new Constraint('qty', '>=', 0));
		$sh->addConstraint(new Constraint('error_qty', '>=', 0));

		parent::index($related_collection, $sh);

		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'stitems');
		$this->view->set('linkvaluefield', 'stitem_id');
		$this->view->set('collection',$related_collection);
		$this->view->set('no_ordering',true);

	}

	public function save_transactions()
	{
		if (!$this->CheckParams('STTransaction'))
		{
			sendBack();
		}

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		$data = $this->_data['STTransaction'];

		$stitem = DataObjectFactory::Factory('STItem');

		$stitem->load($data['stitem_id']);

		$converted = round($data['qty'], $stitem->qty_decimals);

		if ($converted<>$data['qty'])
		{
			$errors[] = 'Quantity can only have '.$stitem->qty_decimals.' decimal places';
		}
		elseif ($data['qty']<=0)
		{
			$errors[] = 'Quantity must be greater than zero';
		}
		else
		{
			$models = STTransaction::prepareMove($data, $errors);

			if (count($errors)==0)
			{
				foreach ($models as $model)
				{
					$result = $model->save($errors);
					if($result===false)
					{
						$db->FailTrans();
					}
				}
			}
		}

		if (count($errors)>0)
		{
			$errors[] = 'Error transferring stock';

			$db->FailTrans();

			$db->CompleteTrans();

			$flash->addErrors($errors);

			$this->_data['whaction_id'] = $data['whaction_id'];

			$this->refresh();

			return;
		}
		else
		{
			$db->CompleteTrans();

			$flash->addMessage('Transfer completed successfully');
		}

		if (isset($this->_data['saveAnother']))
		{
			$this->_data['whaction_id']			= $data['whaction_id'];
			$_POST[$this->modeltype]['qty']		= '';
			$_POST[$this->modeltype]['balance']	= '';

			$this->refresh();
		} else {
			sendTo($_SESSION['refererPage']['controller']
				 , $_SESSION['refererPage']['action']
				 , $_SESSION['refererPage']['modules']
				 , $_SESSION['refererPage']['other'] ?? null);
		}

	}

	/*
	* Copy of code from MfworkordersController - consider moving this to e.g. STTransaction
	* as a common function called from here and MfworkordersController
	*/
	public function getTransferDetails ($_whaction_id = '', $_works_schedule_id = '', $_stitem_id = '', $_type_text = '') {
		// This method is not finished!
		$logger = uzLogger::Instance();
		$logger->warning('WorkSchedulesController::getTransferDetails() is not complete, needs fixing');
		return
		
		$modeltype='STTransaction';

// Used by Ajax to get the From/To Locations/Bins based on Stock Item
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['entry_point'])) { $_entry_point = $this->_data['entry_point']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id = $this->_data['whaction_id']; }
			if(!empty($this->_data['type_text'])) { $_type_text = $this->_data['type_text']; }
			if(!empty($this->_data['works_schedule_id'])) { $_works_schedule_id = $this->_data['works_schedule_id']; }
			if(!empty($this->_data['stitem_id'])) { $_stitem_id = $this->_data['stitem_id']; }
			if(!empty($this->_data['from_whlocation_id'])) { $_from_location_id = $this->_data['from_whlocation_id']; }
			if(!empty($this->_data['from_whbin_id'])) { $_from_bin_id = $this->_data['from_whbin_id']; }
			if(!empty($this->_data['to_whlocation_id'])) { $_to_location_id = $this->_data['to_whlocation_id']; }
		}
		else
		{
// if this is Save and Add Another then need to get $_POST values to set context
			$_stitem_id=$_POST[$modeltype]['stitem_id'] ?? $_stitem_id;
			$_from_location_id=$_POST[$modeltype]['from_whlocation_id'] ?? '';
		}

		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

// ****************************************************************************
// Get the From Locations for the selected action
		$from_locations = $this->getFromLocations($_whaction_id);

		$from_whlocation_ids = array_keys($from_locations);

		if (empty($_entry_point) || $_entry_point==$modeltype.'_whaction_id' || $_entry_point==$modeltype.'_stitem_id')
		{
			if (empty($_from_location_id) || !isset($from_locations[$_from_location_id]))
			{
				$_from_location_id = key($from_locations);
			}
			$output['from_whlocation_id'] = array('data'=>$from_locations, 'is_array'=>is_array($from_locations));
		}
		elseif (empty($_from_location_id) || !isset($from_locations[$_from_location_id]))
		{
			$_from_location_id = key($from_locations);
		}

		$from_location = DataObjectFactory::Factory('WHLocation');
		$from_location->load($_from_location_id);

// ****************************************************************************
// Get the Stock Item list if no stock item is selected
		$stitem = DataObjectFactory::Factory('STItem');

		if (empty($_entry_point) && empty($_stitem_id))
		{
// No item selected so get list of items and set default as first in list
			$stock_items = array();

			if ($from_location->haveBalances($from_whlocation_ids))
			{
				$stock_items = STBalance::getStockList($from_whlocation_ids);
			}
			else
			{
				$stock_items = $stitem->getAll();
			}

			if (empty($_stitem_id))
			{
				$_stitem_id = key($stock_items);
			}
			$output['stitem_id'] = array('data'=>$stock_items, 'is_array'=>is_array($stock_items));
		}

		if (empty($_entry_point) || $_entry_point==$modeltype.'_whaction_id' || $_entry_point==$modeltype.'_stitem_id') {
			$_entry_point = $modeltype.'_from_whlocation_id';
		}

		$stitem->load($_stitem_id);

		$output['uom_name'] = array('data'=>$stitem->uom_name, 'is_array'=>is_array($stitem->uom_name));

		//$required_qty	= round($structure->requiredQty(), $stitem->qty_decimals);
		//$issued_qty		= round($structure->getTransactionBalance(TRUE), $stitem->qty_decimals);
		//$used_qty		= round($structure->getTransactionBalance(FALSE),$stitem->qty_decimals);
		//$required_qty	= round($required_qty - $issued_qty - $used_qty, $stitem->qty_decimals);
		//$required_qty = $required_qty < 0 ? 0: $required_qty;
		//$output['required_qty'] = array('data'=>$required_qty, 'is_array'=>FALSE);
		//$output['issued_qty'] = array('data'=>$issued_qty, 'is_array'=>FALSE);
		//$output['used_qty'] = array('data'=>$used_qty, 'is_array'=>FALSE);

// ****************************************************************************
// Get the list of bins for the To Location if it is bin controlled
		if ($_entry_point==$modeltype.'_from_whlocation_id')
		{
			$from_bins = array();

			if ($from_location->isBinControlled())
			{
				$from_bins = $stitem->getBinList($_from_location_id);

				// check if the input bin present and exists in the bin list
				// if not, check for an error (exists in post data)
				// then check if in bin list; if not, use first in bin list
				if (empty($_from_bin_id) || !isset($from_bins[$_from_bin_id]))
				{
					if (isset($_POST[$modeltype]['from_whbin_id']))
					{
						$_from_bin_id = $_POST[$modeltype]['from_whbin_id'];

						if (!isset($from_bins[$_from_bin_id]))
						{
							$_from_bin_id = key($from_bins);
						}
					}
					else
					{
						$_from_bin_id = key($from_bins);
					}
				}
			}
			else
			{
				$_from_bin_id = '';
			}

			$output['from_whbin_id']=array('data'=>$from_bins,'is_array'=>is_array($from_bins));
		}

// ****************************************************************************
// Get the balance of the selected Item for the selected From Location/Bin
		if ($from_location->isBalanceEnabled())
		{
			$balance = $this->getBalance($_stitem_id, $_from_location_id, $_from_bin_id);
		} else {
			$balance = '-';
		}

		$output['balance'] = array('data'=>$balance, 'is_array'=>is_array($balance));

// ****************************************************************************
// get the associated 'To Location' values for the selected from location
		if ($_entry_point==$modeltype.'_from_whlocation_id')
		{
			$to_locations=$this->getToLocations($_from_location_id, $_whaction_id);
			$_to_location_id = key($to_locations);

			$output['to_whlocation_id'] = array('data'=>$to_locations, 'is_array'=>is_array($to_locations));
			$_entry_point=$modeltype.'_to_whlocation_id';
		}

		$to_location = DataObjectFactory::Factory('WHLocation');
		$to_location->load($_to_location_id);

// ****************************************************************************
// Get the bin list for the To Location if it is bin controlled
		if ($_entry_point==$modeltype.'_to_whlocation_id')
		{
			$to_bins = array();

			if ($to_location->isBinControlled())
			{
				$to_bins = $this->getBinList($_to_location_id);
			}
			$output['to_whbin_id'] = array('data'=>$to_bins, 'is_array'=>is_array($to_bins));
		}

// ****************************************************************************
// Get list of transactions for the action and works order
		$sttransactions = new STTransactionCollection();
		$sh = new SearchHandler($sttransactions, false);
		$sh->addConstraint(new Constraint('process_name', '=', $this->_templateobject->transaction_type()));
		$sh->addConstraint(new Constraint('process_id', '=', $_works_schedule_id));
		$sh->addConstraint(new Constraint('whaction_id', '=', $_whaction_id));
		$sh->addConstraint(new Constraint('qty', '>', 0));
		$sh->setFields(array( 'id'
							, 'stitem as Stock_Item'
							, 'stitem_id'
							, 'flocation as from_location'
							, 'fbin as from_bin'
							, 'whlocation as to_location'
							, 'whbin as to_bin'
							, 'qty'));
		$sttransactions->load($sh);

		$this->view->set('clickmodule', $this->_modules);
		$this->view->set('clickcontroller', 'stitems');
		$this->view->set('clickaction', 'view');
		$this->view->set('linkvaluefield', 'stitem_id');
		$this->view->set('collection', $sttransactions);

		$this->view->set('type_text', $_type_text);
		$this->view->set('page_title', $this->getPageName(null, $_type_text.' for '));

		$html=$this->view->fetch($this->getTemplateName('en_issues_list'));
		$output['sttransactions'] = array('data'=>$html, 'is_array'=>is_array($html));

// ****************************************************************************
// Finally, if this is an ajax call, set the return data area
		if ($ajax)
		{
			$this->view->set('data', $output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $output;
		}

	}

	public function getBalance($_stitem_id = '', $_location_id = '', $_bin_id = '')
	{
// Function called by Ajax Request to return balance for selected item, location, bin
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['stitem_id'])) { $_stitem_id = $this->_data['stitem_id']; }
			if(!empty($this->_data['whlocation_id'])) { $_location_id = $this->_data['whlocation_id']; }
			if(!empty($this->_data['whbin_id'])) { $_bin_id = $this->_data['whbin_id']; }
		}

		$balance = DataObjectFactory::Factory('STBalance');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('stitem_id', '=', $_stitem_id));
		$cc->add(new Constraint('whlocation_id', '=', $_location_id));

		if (!empty($_bin_id) && $_bin_id!="null")
		{
			$cc->add(new Constraint('whbin_id', '=', $_bin_id));
		}

		$balance->loadBy($cc);

		$balances = ($balance->isLoaded())?$balance->balance:0;

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $balances);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $balances;
		}

	}

	public function getFromLocations($_whaction_id = '')
	{
	// used by ajax to get a list of locations for a given WH Action and From Location

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['whaction_id'])) { $_whaction_id = $this->_data['whaction_id']; }
		}

		$transfer_rule = DataObjectFactory::Factory('WHTransferrule');
		$locations = $transfer_rule->getFromLocations($_whaction_id);

		if(isset($this->_data['ajax'])) {
			$this->view->set('options', $locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}

	}

	public function getToLocations($_whlocation_id = '', $_whaction_id = '')
	{
	// used by ajax to get a list of locations for a given WH Action and From Location

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['whlocation_id'])) { $_whlocation_id = $this->_data['whlocation_id']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id = $this->_data['whaction_id']; }
		}

		$transfer_rule = DataObjectFactory::Factory('WHTransferrule');
		$locations = $transfer_rule->getToLocations($_whaction_id, $_whlocation_id);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}

	}

/* protected functions */
	protected function getPageName($base = null, $action = null)
	{

		$base = empty($base)?$this->_templateobject->getTitle():$base;

		return parent::getPageName(empty($base)?'work_schedules':$base, $action);

	}


/* private functions */

}

// end of WorkschedulesController
