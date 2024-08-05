<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class MfworkordersController extends ManufacturingController
{

	protected $version = '$Revision: 1.72 $';

	protected $_templateobject;
	protected $module_prefs;

	use getSalesOrderOptions;

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('MFWorkorder');
		$this->uses($this->_templateobject);

		// Get module preferences
		$this->module_prefs = ManufacturingController::getPreferences();
		$this->view->set('module_prefs', $this->module_prefs);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$errors = array();

		$s_data = array();

		if (isset($this->_data['Search']['wo_number']) && !empty($this->_data['Search']['wo_number']))
		{
			$this->_data['Search']['wo_number'] = intval($this->_data['Search']['wo_number']);
		}


		$this->setSearch('workordersSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'view');

		$workorders = new MFWorkorderCollection($this->_templateobject);

		parent::index($workorders);

		$workorder_objects = $workorders->getContents();

		$num_incomplete = 0;

		foreach ($workorder_objects as $workorder)
		{
			if ($workorder->_data['status'] != 'C')
			{
				$num_incomplete++;
			}
		}

		$this->view->set('num_incomplete', $num_incomplete);

		$sidebar = new SidebarController($this->view);

		$sidebar->addList(
			'Actions',
			array('new'=>array('tag'=>'New Works Order'
							  ,'link'=>array('modules'=>$this->_modules
											,'controller'=>$this->name
											,'action'=>'new'
											)
				 			  )
				 ,'fulfilled'=>array('tag'=>'Show Open/Fulfilled'
									,'link'=>array('modules'=>$this->_modules
												  ,'controller'=>$this->name
												  ,'action'=>'showFulfilled'
												  )
									)
				)
			);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function completeStatus()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction		 = $this->_uses[$this->modeltype];
		$transaction->status = 'C';

		$flash = Flash::Instance();

		$id = $this->_data['id'];

		$errors = array();

		$transaction->save($errors);

		if (count($errors)==0)
		{
			$flash->addMessage('Order is now complete');
		}

		sendTo($this->name
				,'view'
				,$this->_modules
				,array('id' => $id));

	}

	public function batchUpdate()
	{
		if (!isset($this->_data['update']) && !isset($this->_data['print']))
		{
			sendTo($this->name
					,'index'
					,$this->_modules);
		}

		$update = $this->_data['update'];
		$print = $this->_data['print'];

		$flash = Flash::Instance();

		$errors = array();

		foreach ($update as $id => $value)
		{
			$transaction = DataObjectFactory::Factory('MFWorkorder');
			$transaction->load($id);
			$transaction->status = $this->_data['status'][$id];
			$transaction->save($errors);
		}

		if (count($errors) == 0)
		{
			$flash->addMessage('Selected Work Orders have been updated');
		}

		if ($this->module_prefs['allow-wo-print'] !== 'D') {
			foreach ($print as $id => $value) {
				$worksorder = DataObjectFactory::Factory('MFWorkorder');
				$worksorder->load($id);
				if (isset($this->_data['update']) && ($this->_data['status'][$id] == 'C' && $this->_data['update'][$id] == 'on')) {
					// skip printing if the status is changing to complete
					continue;
				}

				if (unserialize($worksorder->documentation)[0]=='') {
					continue;
				} // No documents selected on this work order
				$documents = InjectorClass::unserialize($worksorder->documentation);

				$userPreferences	= UserPreferences::instance(EGS_USERNAME);
				$defaultPrinter		= $userPreferences->getPreferenceValue('default_printer', 'shared');

				$data				 = [];
				$data['id']			 = $id;
				$data['printtype']	 = 'pdf';
				$data['printaction'] = 'Print';
				$data['printer']	 = $defaultPrinter;

				$merge_file_name = 'mfworksorders_documentation_'.$id.'_'.date('H_i_s_d_m_Y').'.pdf';

				foreach ($documents as $document)
				{
					// when we fire the construct, pass the printController as the report does
					// not extend another model
					$model = new $document->class_name($this);
					$docname = rand().'.pdf';

					$args = array(
						'model'				=>	$worksorder,
						'data'				=>	$data,
						'merge_file_name'	=>	$merge_file_name,
						'type' => 'print',
						'printtype'	 => 'pdf'
					);

					$response = $model->buildReport($args);

					if($response->status!==true)
					{
						$errors[] = $document->class_name.": ".$response->message;
					}
				}

				$merge_file_path = $this->get_filetype_path('tmp').$merge_file_name;

				$attachment_paths = $this->createAttachmentOutputFiles($worksorder->stitem_id);
				if (count($attachment_paths) > 0){
					foreach ($attachment_paths as $file){
						$response = PDFTools::append($file, $merge_file_path);
					}
				}

				$this->output_file_to_printer($merge_file_path, $data['printer']);

				if (count($errors)>0)
				{
					$flash->addErrors($errors);
				} else {
					$flash->addMessage('Work Order Documentation Printed');
				}
			}
		}

		sendTo($this->name
				,'index'
				,$this->_modules);
	}

	public function _new()
	{

		// need to store the ajax flag in a different variable and the unset the original
		// this is to prevent any functions that are further called from returning the wrong datatype
		$ajax = isset($this->_data['ajax']);

		unset($this->_data['ajax']);

		parent::_new();

		$stitems = STItem::nonObsoleteItems(null,'M');

		$this->view->set('stitems', $stitems);

		$stitem = DataObjectFactory::Factory('STItem');

		if (isset($this->_data['stitem_id']))
		{
			$stitem_id = $this->_data['stitem_id'];

			$stitem->load($stitem_id);

			$this->view->set('stitem', $stitem->item_code.' - '.$stitem->description);
		}
		else
		{
			$stitem_id = key($stitems);
		}

		$this->view->set('uoms', $this->getUomList($stitem_id));

		$wodocs = new InjectorClassCollection(DataObjectFactory::Factory('InjectorClass'));

		$wodocs->getClassesList('WO');

		$this->view->set('documents', $wodocs->getAssoc('name'));
		$this->view->set('selected_docs', $this->module_prefs['default-wo-docs']);

		// We only want non-archived projects
		$projects = Project::getLiveProjects();
		$this->view->set('projects', $projects);


		$order_id = (empty($this->_data['order_id']))?'':$this->_data['order_id'];

		$this->view->set('sales_orders', $this->getSalesOrders($order_id));

		if (!empty($order_id))
		{
			$orderline_id = (empty($this->_data['orderline_id']))?'':$this->_data['orderline_id'];

			$orderlines = $this->getOrderLines($order_id, $orderline_id);
		}
		else
		{
			$orderlines = array();
		}

		$this->view->set('order_lines', $orderlines);
	}

	public function edit()
	{
		parent::edit();

		$this->view->set('stitem', $this->_uses[$this->modeltype]->stitem);

		$this->view->set('uoms', $this->getUomList($this->_uses[$this->modeltype]->stock_item->id));

		foreach ($this->_uses[$this->modeltype]->getDocumentList() as $document)
		{
			$selected_docs[$document->id] = $document->id;
		}

		$this->view->set('selected_docs', $selected_docs);

		$this->view->set('sales_orders', $this->getSalesOrders($this->_uses[$this->modeltype]->order_id));

		$orderlines = array();

		if (!is_null($this->_uses[$this->modeltype]->order_id))
		{
			$orderlines = $this->getOrderLines($this->_uses[$this->modeltype]->order_id, $this->_uses[$this->modeltype]->orderline_id);
		}

		$this->view->set('order_lines', $orderlines);
	}

	public function delete($modelName = null)
	{
		if (!$this->CheckParams($this->_templateobject->idField))
		{
			sendBack();
		}

		$flash = Flash::Instance();

		parent::delete($this->modeltype);

		sendTo($this->name, 'index', $this->_modules);
	}

	public function save($modelName = null, $dataIn = [], &$errors = []) :void
	{

		if (!$this->CheckParams($this->modeltype))
		{
			sendBack();
		}

		$flash = Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors = array();

		if(isset($this->_data[$this->modeltype]['documentation']))
		{
			$this->_data[$this->modeltype]['documentation'] = serialize($this->_data[$this->modeltype]['documentation']);
		}
		else
		{
			// If no documents set, set as a blank array.
			$this->_data[$this->modeltype]['documentation'] = serialize(array('0'=>''));
		}

		if (isset($this->_data['saveAnother']))
		{
			unset($this->_data['saveAnother']);
			$saveanother = true;
		}
		else
		{
			$saveanother = false;
		}

		if ($this->_data[$this->modeltype]['order_qty']<=0)
		{
			$errors['order_qty'] = 'Order Quantity must be greater than zero';
		}
		elseif(parent::save_model($this->modeltype))
		{
			$data = $this->saved_model;

			if (!MFWOStructure::exists($data->id))
			{
				$models = MFWOStructure::copyStructure($data, $errors);

				foreach ($models as $model)
				{
					$result=$model->save($errors);

					if($result===false)
					{
						break;
					}
				}

				if ($result===false || count($errors)>0)
				{
					$errors[] = 'Error creating Works Order Product Structure';
				}
				else
				{
					$flash->addMessage('Creation of Works Order Product Structure completed successfully');
				}
 			}
		}
		else
		{
			$errors[]='Failed to save Works Order';
		}

		if (count($errors)==0 && $db->CompleteTrans())
		{
			if ($saveanother)
			{
				$this->refresh();
				return;
			}
			else
			{
				 sendTo('MFWorkorders',
				 		'view',
				 		$this->_modules,
				 		array('id'=>$data->id));
			}
		}

		$db->FailTrans();
		$db->CompleteTrans();

		$this->_data['stitem_id']=$this->_data[$this->modeltype]['stitem_id'];

		$this->view->set('selected_docs', InjectorClass::unserialize($this->_data[$this->modeltype]['documentation']));

		$flash->addErrors($errors);

		$this->refresh();
	}

	public function showFulfilled()
	{
		$mfworkorders = new MFWorkorderCollection($this->_templateobject);

		$sh = new SearchHandler($mfworkorders);

		$sh->extract();

		$sh->addConstraint(new Constraint('status', '=', 'O'));
		$sh->addConstraint(new Constraint('made_qty', '>=', '(order_qty)'));

		$sh->extractOrdering();

		$sh->extractPaging();

		$mfworkorders->load($sh);

		$this->view->set('clickaction', 'view');
		$this->view->set('mfworkorders',$mfworkorders);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll'] = array('tag'	=> 'View'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'index'
														)
									  );
		$sidebar->addList('All Works Orders',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function view()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];

		$this->view->set('documentation', InjectorClass::unserialize($transaction->documentation));
		$this->view->set('transaction',$transaction);

		$id = $transaction->id;

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll'] = array('tag'	=> 'View'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'index'
														)
									  );

		$sidebarlist['newOrder'] = array('tag'	=> 'New Works Order'
										,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'new'
														)
									   );

		$sidebar->addList('All Works Orders',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['view'] = array('tag'	=> 'view'
									,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'view'
													,'id'			=> $id
													)

									);

		$sidebarlist['edit'] = array('tag'	=> 'Edit'
									,'link'	=> array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'edit'
													,'id'			=> $id
													)
                					);

		$sidebarlist['reviewMaterials'] = array('tag'	=> 'Review Materials'
											   ,'link'	=> array('modules'		=> $this->_modules
																,'controller'	=> $this->name
																,'action'		=> 'reviewMaterials'
																,'id'			=> $id
																)
                					);

		$sidebarlist['reviewResources']= array('tag' => 'Review Resources'
											  ,'link' => array('modules'=>$this->_modules
															  ,'controller'=>$this->name
															  ,'action'=>'reviewResources'
															  ,'id'=>$id
															  ,'stitem_id'=>$transaction->stitem_id
															  )
                							  );

		if ($transaction->status != 'C')
		{
			$tag = 'Change Status to Complete';

			if ($transaction->made_qty < $transaction->order_qty)
			{
				$tag = 'Force Complete';
			}

			$sidebarlist['complete'] = array('tag'	=> $tag
											,'link'	=> array('modules'		=> $this->_modules
															,'controller'	=> $this->name
															,'action'		=> 'completeStatus'
															,'id'			=> $id
															)
										   );
		}
		if ($transaction->status=='R')
		{
			$sidebarlist['resetStatus'] = array('tag'	=> 'Reset Status'
											   ,'link'	=> array('modules'		=> $this->_modules
																,'controller'	=> $this->name
																,'action'		=> 'resetStatus'
																,'id'			=> $id
																)
										   );
		}

		$whaction = DataObjectFactory::Factory('WHAction');

		$sidebarlist['issues'] = array('tag'	=> 'Issues'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'issues_returns'
														,'id'			=> $id
														,'type'			=> $whaction->getEnumKey('type', 'Issue')
														)
									   );
		$sidebarlist['returns'] = array('tag'	=> 'Returns'
									   ,'link'	=> array('modules'		=> $this->_modules
														,'controller'	=> $this->name
														,'action'		=> 'issues_returns'
														,'id'			=> $id
														,'type'			=> $whaction->getEnumKey('type', 'Return')
														)
									   );
		$sidebarlist['book'] = array('tag'	=> 'Book Production'
									,'link' => array('modules'		=> $this->_modules
													,'controller'	=> $this->name
													,'action'		=> 'bookproduction'
													,'id'			=> $id
													,'stitem_id'	=> $transaction->stitem_id
													)
									   );

		$sidebar->addList('This Works Order',$sidebarlist);

		$this->sidebarRelatedItems($sidebar, $transaction);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('printers', $this::selectPrinters());
		$this->view->set('default_printer', $this->getDefaultPrinter());

	}

	public function resetStatus()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$flash = Flash::Instance();

		$transaction = $this->_uses[$this->modeltype];

		// check again if WO is released only
		if ($transaction->status=='R')
		{
			if(isset($this->_data['type'])&&($this->_data['type']=='reset'))
			{
				if($transaction->update($this->_data['id'], 'status', 'N'))
				{
					$flash->addMessage('Works Order Status Reset Successful');

					sendTo($_SESSION['refererPage']['controller']
						  ,$_SESSION['refererPage']['action']
						  ,$_SESSION['refererPage']['modules']
						  ,$_SESSION['refererPage']['other'] ?? null);
				}
				else
				{
					$flash->addError('Works Order Status Reset Failed');

					sendTo($_SESSION['refererPage']['controller']
						  ,$_SESSION['refererPage']['action']
						  ,$_SESSION['refererPage']['modules']
						  ,$_SESSION['refererPage']['other'] ?? null);
				}
			}

			if(isset($this->_data['type'])&&($this->_data['type']=='cancel'))
			{
				$flash->addError('Works Order Status Reset Cancelled');

				sendTo($_SESSION['refererPage']['controller']
					  ,$_SESSION['refererPage']['action']
					  ,$_SESSION['refererPage']['modules']
					  ,$_SESSION['refererPage']['other'] ?? null);
			}

			$this->view->set('id', $this->_data['id']);

		}
		else
		{
			$flash->addError('Cannot Change Works Order Status');

			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,$_SESSION['refererPage']['other'] ?? null);
		}
	}

	public function reviewMaterials()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];
		$id			 = $transaction->id;

		$this->view->set('transaction', $transaction);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll'] = array('tag' => 'View'
									   ,'link' => array('modules'=>$this->_modules
													   ,'controller'=>$this->name
													   ,'action'=>'index'
													   )
									  );

		$sidebarlist['New'] = array('tag' => 'New Works Order'
								   ,'link' => array('modules'=>$this->_modules
												   ,'controller'=>$this->name
												   ,'action'=>'new'
												   )
								  );

		$sidebar->addList('All Works Order',$sidebarlist);

		$sidebarlist = array();

		$sidebarlist['viewThis'] = array('tag' => 'View'
										,'link' => array('modules'=>$this->_modules
														,'controller'=>$this->name
														,'action'=>'view'
														,'id'=>$id
														)
									   );

		$sidebarlist['Edit'] = array('tag' => 'Edit'
									,'link' => array('modules'=>$this->_modules
													,'controller'=>$this->name
													,'action'=>'edit'
													,'id'=>$id
													)
									);

		if ($transaction->status == 'N')
		{
			$sidebarlist['Add'] = array('tag' => 'Add to Structure'
									   ,'link' => array('modules'=>$this->_modules
													   ,'controller'=>'MFWOStructures'
													   ,'action'=>'new'
													   ,'work_order_id'=>$id
													   )
									  );
		}

		$sidebar->addList('This Works Order',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$elements = new MFWOStructureCollection();

		$elements->orderby = 'line_no';
		$sh = $this->setSearchHandler($elements);
		$sh->addConstraint(new Constraint('work_order_id', '=', $id));

		$sh->setFields(array('id', 'line_no', 'work_order_id', 'ststructure_id', 'ststructure', 'uom_id', 'uom', 'qty', 'waste_pc'));

		parent::index($elements, $sh);

		$this->_templateName = $this->getTemplateName('reviewMaterials');
		$this->view->set('clickaction','edit');
		$this->view->set('clickcontroller','MFWostructures');

		if ($transaction->status == 'N')
		{
			$this->view->set('cell','1');
		}
		else
		{
			$this->view->set('cell','10');
		}

	}

	public function reviewResources()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];
		$id			 = $transaction->id;

		$this->view->set('transaction',$transaction);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['viewAll']= array('tag' => 'View'
									  ,'link' => array('modules'=>$this->_modules
													  ,'controller'=>$this->name
													  ,'action'=>'index'
													  )
									  );

		$sidebarlist['newOrder']= array('tag' => 'New Works Order'
									   ,'link' => array('modules'=>$this->_modules
													   ,'controller'=>$this->name
													   ,'action'=>'new'
													   )
										);

		$sidebar->addList('All Works Orders',$sidebarlist);

		$sidebar->addList(
			'This Works Order',
			array('viewThis' => array('tag' => 'View'
									 ,'link' => array('modules'=>$this->_modules
													 ,'controller'=>$this->name
													 ,'action'=>'view'
													 ,'id'=>$id
													 )
									)
				,'edit' => array('tag' => 'Edit'
								,'link' => array('modules'=>$this->_modules
												,'controller'=>$this->name
												,'action'=>'edit'
												,'id'=>$id
												)
								)
				)
			);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$stockitem = DataObjectFactory::Factory('STItem');
		$stockitem->load($transaction->stitem_id);
		$this->view->set('stockitem',$stockitem);

		$elements = new MFOperationCollection();

		$elements->orderby = 'op_no';

		$sh = $this->setSearchHandler($elements);

		$cc = new ConstraintChain;
		$cc->add(new Constraint('stitem_id', '=', $transaction->stitem_id));
		$cd = currentDateConstraint();
		$cc->add($cd);
		$sh->addConstraintChain($cc);

		$sh->setFields(array('id', 'op_no', 'remarks', 'centre', 'resource', 'resource_qty', 'volume_period', 'volume_target', 'type', 'volume_uom_id'));

		parent::index($elements, $sh);

		$this->_templateName = $this->getTemplateName('reviewResources');

		$this->view->set('clickcontroller','mfoperations');
		$this->view->set('clickaction','edit');

		$this->view->set('page_title', $this->getPageName('Works Order', 'Review Resources for'));

	}

	public function bookproduction()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];

		$this->view->set('transaction',$transaction);

		$stitem_id = $transaction->stitem_id;

// Get the Completion action associated with the stock item
// for this Works Order
		$stitem = DataObjectFactory::Factory('STItem');
		$stitem->load($stitem_id);

		$this->view->set('item_code', $stitem->item_code);

		if ($stitem)
		{
			$whaction_id = $stitem->getAction('complete');

			if (!empty($whaction_id))
			{
				$this->displayLocations($whaction_id);
			}
			else
			{
				$flash = Flash::Instance();

				$flash->addWarning('Complete action not found for this stock item');
			}
		}
		else
		{
			$whaction_id = 0;
		}

		$this->view->set('whaction_id', $whaction_id);

	}

	public function updatewip()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$worksorder = $this->_uses[$this->modeltype];

		$flash = Flash::Instance();

		$errors = array();

		$data = $this->_data[$this->modeltype];

		$id			= $data['id'];
		$stitem_id	= $data['stitem_id'];

// Insert transaction pair for WIP Update
		$data['qty']			= $data['book_qty'];
		$data['process_name']	= 'WO';
		$data['process_id']		= $id;

		$db = DB::Instance();
		$db->StartTrans();

		if ($data['qty']>0)
		{
			$models = STTransaction::prepareMove($data, $errors);
		}
		else
		{
			$errors[] = 'Quantity must be greater than zero';
		}

		if (count($errors)==0)
		{
			foreach ($models as $model)
			{
				if (!$model->save($errors))
				{
					$errors[] = 'Error transferring stock';
					break;
				}
			}
		}
		if (count($errors)==0)
		{
			$worksorder->status		= 'O';
			$worksorder->made_qty	= bcadd($worksorder->made_qty, trim((string) $data['book_qty']), 0);

			// Mark complete when total booking is equal or greater than the order quantity.
			if ($worksorder->made_qty >= $worksorder->order_qty && $this->module_prefs['complete-wo-full'] === 'on') {
				$worksorder->status = 'C';
			}

			if (!$worksorder->save())
			{
				$errors[] = 'Error updating Works Order';
			}
		}

		if (count($errors)>0)
		{
			$db->FailTrans();
		}

		$db->CompleteTrans();

// If all OK, do backflush; this has to be outside of the above transaction
// because backflushing can fail, but this is OK as it is handled separately

		if (count($errors)==0)
		{
			if (MFStructure::backflush($data, $worksorder->structureitems, $errors))
			{
				$flash->addMessage('Transfer completed successfully');
				$flash->addMessage('Works Order Updated');

				if ($worksorder->made_qty>=$worksorder->order_qty)
				{
					$flash->addMessage('Order Quantity has been fulfilled');
				}

				sendTo($_SESSION['refererPage']['controller']
					  ,$_SESSION['refererPage']['action']
					  ,$_SESSION['refererPage']['modules']
					  ,$_SESSION['refererPage']['other'] ?? null);
			}
			else
			{
				$errors[] = 'Serious error trying to backflush - PLEASE REPORT IMMEDIATELY';
			}
		}

		$errors[] = 'Error booking production';

		$debug = Debug::Instance();

		$body = "MfworkordersController::updatewip\n"
			 ."at ".date(DATE_TIME_FORMAT)."\n\n"
			 ."User               ".EGS_USERNAME."\n"
			 ."Works Order Id     ".$worksorder->id."\n"
			 ."Works Order Number ".$worksorder->wo_number."\n"
			 ."Stock Item Id      ".$worksorder->stitem_id."\n"
			 ."Booking Qty        ".$data['book_qty']."\n\n";

		foreach ($errors as $error)
		{
			$body.=$error."\n";
		}

		$subject = get_config('SYSTEM_STATUS');
		$subject = (!empty($subject) ? $subject : 'system');

		system_email($subject.' Error', $body, $errors);

		$flash->addErrors($errors);

		sendTo($this->name
				,'bookproduction'
				,$this->_modules
				,array('id' => $id,'stitem_id' => $stitem_id));
	}


	public function view_purchases()
	{
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$works_order = $this->_uses[$this->modeltype];

		$polines = DataObjectFactory::Factory('POrderLine');

		$polines->setDefaultDisplayFields(array(		'order_id',
		'order_number',
		'description',
		'operation',
		'due_delivery_date' => 'expected_return_date',
		'status',
		'order_qty',
		'os_qty',
		'revised_qty',
		'del_qty',
		'price',
		'uom_name'));

		$related_collection = new POrderLineCollection($polines);

		$sh = $this->setSearchHandler($related_collection);

		$sh->addConstraint(new Constraint('mf_workorders_id', '=', $works_order->id));
		$sh->addConstraint(new Constraint('status', '!=', $polines->cancelStatus()));

		parent::index($related_collection, $sh);

		$this->_templateName = $this->getTemplateName('view_related');
		$this->view->set('clickaction', 'view');;
		$this->view->set('related_collection', $related_collection);
		$this->view->set('collection', $related_collection);
		$this->view->set('no_ordering', true);
	}


	public function view_Transactions ()
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
		$sh->addConstraint(new Constraint('process_name', '=', 'WO'));
		$sh->addConstraint(new Constraint('qty', '>=', 0));
		$sh->addConstraint(new Constraint('error_qty', '>=', 0));

		parent::index($related_collection, $sh);

		$this->_templateName = $this->getTemplateName('view_related');
		$this->view->set('clickaction', 'view');
		$this->view->set('clickcontroller', 'stitems');
		$this->view->set('linkvaluefield', 'stitem_id');
		$this->view->set('related_collection', $related_collection);
		$this->view->set('collection', $related_collection);
		$this->view->set('no_ordering', true);

	}

	public function issues_returns()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$transaction = $this->_uses[$this->modeltype];

		$structure = DataObjectFactory::Factory('MFWOStructure');

		$structure->idField			= 'ststructure_id';
		$structure->identifierField	= 'ststructure';

		$cc = new ConstraintChain();
		$cc->add(new Constraint('work_order_id', '=', $transaction->id));

		$structure_items = $structure->getAll($cc, true, true);

		$this->view->set('structure_items', $structure_items);

		$whaction = DataObjectFactory::Factory('WHAction');

		if (empty($this->_data['type']))
		{
			$this->_data['type'] = $whaction->getEnumKey('type', 'Issue');
		}
		$type_text = $whaction->getEnum('type', $this->_data['type']);

		$actions = $whaction->getActions($this->_data['type']);

		if (empty($actions))
		{
			$flash = Flash::instance();
			$flash->addError('No '.$type_text.' Actions Defined');
			sendBack();
		}

		$this->view->set('actions', $actions);

		$this->getTransferDetails(key($actions), $transaction->id, key($structure_items), $type_text);

		$sttransaction = DataObjectFactory::Factory('STTransaction');

		$this->view->set('process_id', $transaction->id);
		$this->view->set('process_name', 'WO');
		$this->view->set('id', $this->_data['id']);
		$this->view->set('type', $this->_data['type']);

		$this->uses($sttransaction, false);

		parent::_new();

	}

	public function printAction ()
	{
        $userPreferences	= UserPreferences::instance(EGS_USERNAME);
        $defaultPrinter		= $userPreferences->getPreferenceValue('default_printer', 'shared');

        if(empty($defaultPrinter))
        {
        	// Use normal print action
        	parent::printAction();
        	$this->printtype	= array('pdf'=>'PDF');
			$this->printaction	= array('Print'=>'Print');
        }
        else
        {
        	// Overide print action
        	$data				 = array();
        	$data['id']			 = $this->_data['id'];
        	$data['printtype']	 = 'pdf';
        	$data['printaction'] = 'Print';
        	$data['printer']	 = $defaultPrinter;

			sendTo($this->name, $this->_data['printaction'], $this->_modules, $data);
        }
	}

	public function printdocumentation()
	{
		$flash = Flash::Instance();

		if (isset($this->_data['cancel']))
		{
			$flash->addMessage('Print Works Order Documentation Cancelled');
			sendBack();
		}

		$errors = array();

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		// If this is a post request, check that the user
		// has selected some documents to print
		$request = $this->_injector->getRequest();
		if ( strtolower((string) $request->getMethod()) == 'post' && !isset($this->_data['doc_selection'])) {
		    $flash->addError('No document selected for output');
		    sendBack();
		}

		$worksorder = $this->_uses[$this->modeltype];

		if ($worksorder->status != 'R') {
		    $flash->addError('Work Order must be released to print documents');
		    sendBack();
		}

		$data = $this->_data;

		$docs_count = unserialize($worksorder->documentation);
		if ($this->_data['doc_selection']) {
		    $docs_count = $this->_data['doc_selection'];
		}

		$merge_file_name = 'mfworksorders_documentation_'.$data['id'].'_'.date('H_i_s_d_m_Y').'.pdf';

		// Need to check to see if array count is equal to, or less than 1, and if array element 0 is null
		if(count($docs_count)==0 || $docs_count[0]=='')
		{
			$errors[] = 'No Documents Assigned to Works Order';
		}
		else
		{
			if ($worksorder)
			{
				$documents = InjectorClass::unserialize($worksorder->documentation);

				foreach ($documents as $document)
				{
				    if (!isset($this->_data['doc_selection']) || in_array($document->id, $this->_data['doc_selection'])) {
    					// when we fire the construct, pass the printController as the report does
    					// not extend another model
    					$model = new $document->class_name($this);

    					$args = array(
    						'model'				=>	$worksorder,
    						'data'				=>	$data,
    						'merge_file_name'	=>	$merge_file_name,
    					    'type' => $this->_data['type'],
    					    'printtype'	 => 'pdf'
    					);

    					//$response=$model->buildReport($worksorder,$data);
    					$response = $model->buildReport($args);

    					if($response->status!==true)
    					{
    						$errors[] = $document->class_name.": ".$response->message;
    					}
				    }
				}

			}
			else
			{
				$errors[] = 'Failed to find Works Order';
			}
		}

		if (count($errors)>0)
		{
			$flash->addErrors($errors);
		}
		else
		{

			// construct file path, print the file and add a success message
			$merge_file_path = $this->get_filetype_path('tmp').$merge_file_name;

			// append attachments
			$attachment_paths = $this->createAttachmentOutputFiles($worksorder->stitem_id);
			if (count($attachment_paths) > 0 && !isset($this->_data['original_action'])){
				foreach ($attachment_paths as $file){
					$response = PDFTools::append($file, $merge_file_path);
				}
			}

			if (!isset($this->_data['type']) || $this->_data['type'] === 'print') {
    			$this->output_file_to_printer($merge_file_path, $data['printer']);
			} else {
    			header("Content-type:application/pdf");

    			// It will be called downloaded.pdf
    			header("Content-Disposition:inline;filename='downloaded.pdf'");
    			header('Content-Transfer-Encoding: binary');
    			header('Content-Length: ' . filesize($merge_file_path));
    			header('Accept-Ranges: bytes');

    			// The PDF source is in original.pdf
    			@readfile($merge_file_path);
			}

			$flash->addMessage('Works Order Documentation Completed');
		}

		sendBack();

	}


/* Obsolete?
	public function getBalance() {
	// used by ajax to get a list of bins for a location
		$location=DataObjectFactory::Factory('WHLocation');
		$location->load($this->_data['id']);
		echo json_encode($location->getBinList());
		exit;
	}
*/
	public function getTransferDetails ($_whaction_id = '', $_work_order_id = '', $_stitem_id = '', $_type_text = '') {

		$modeltype = 'STTransaction';

// Used by Ajax to get the From/To Locations/Bins based on Stock Item
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['entry_point'])) { $_entry_point = $this->_data['entry_point']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id = $this->_data['whaction_id']; }
			if(!empty($this->_data['type_text'])) { $_type_text = $this->_data['type_text']; }
			if(!empty($this->_data['work_order_id'])) { $_work_order_id = $this->_data['work_order_id']; }
			if(!empty($this->_data['stitem_id'])) { $_stitem_id = $this->_data['stitem_id']; }
			if(!empty($this->_data['from_whlocation_id'])) { $_from_location_id = $this->_data['from_whlocation_id']; }
			if(!empty($this->_data['from_whbin_id'])) { $_from_bin_id = $this->_data['from_whbin_id']; }
			if(!empty($this->_data['to_whlocation_id'])) { $_to_location_id = $this->_data['to_whlocation_id']; }
		}
		else
		{
// if this is Save and Add Another then need to get $_POST values to set context
			$_stitem_id			= $_POST[$modeltype]['stitem_id'] ?? $_stitem_id;
			$_from_location_id	= $_POST[$modeltype]['from_whlocation_id'] ?? '';
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
			$this->view->set('from_locations', $from_locations);

			if (empty($_from_location_id) || !isset($from_locations[$_from_location_id]))
			{
				$_from_location_id = key($from_locations);
			}

			$this->view->set('from_whlocation', $from_locations[$_from_location_id]);

			$output['from_whlocation_id'] = array('data'=>$from_locations, 'is_array'=>is_array($from_locations));
		}
		elseif (empty($_from_location_id) || !isset($from_locations[$_from_location_id]))
		{
			$_from_location_id = key($from_locations);
		}

		$this->view->set('from_whlocation_id', $_from_location_id);

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

			$this->view->set('stock_item', $stock_items[$_stitem_id]);
			$this->view->set('stock_items', $stock_items);
			$output['stitem_id'] = array('data'=>$stock_items, 'is_array'=>is_array($stock_items));
		}

		if (empty($_entry_point) || $_entry_point==$modeltype.'_whaction_id' || $_entry_point==$modeltype.'_stitem_id')
		{
			$_entry_point=$modeltype.'_from_whlocation_id';
		}

		$stitem->load($_stitem_id);

		$this->view->set('stitem_id',$_stitem_id);
		$this->view->set('uom_name', $stitem->uom_name);

		$output['uom_name'] = array('data'=>$stitem->uom_name, 'is_array'=>is_array($stitem->uom_name));

		$structure = DataObjectFactory::Factory('MFWOStructure');

		$structure->loadBy(array('work_order_id', 'ststructure_id'), array($_work_order_id, $_stitem_id));

		$required_qty	= round($structure->requiredQty(), $stitem->qty_decimals);
		$issued_qty		= round($structure->getTransactionBalance(TRUE), $stitem->qty_decimals);
		$used_qty		= round($structure->getTransactionBalance(FALSE),$stitem->qty_decimals);
		$required_qty	= round($required_qty-$issued_qty-$used_qty, $stitem->qty_decimals);
		$required_qty	= $required_qty<0?0:$required_qty;

		$this->view->set('required_qty', $required_qty);
		$this->view->set('issued_qty', $issued_qty);
		$this->view->set('used_qty', $used_qty);

		$output['required_qty']	= array('data'=>$required_qty, 'is_array'=>FALSE);
		$output['issued_qty']	= array('data'=>$issued_qty, 'is_array'=>FALSE);
		$output['used_qty']		= array('data'=>$used_qty, 'is_array'=>FALSE);

// ****************************************************************************
// Get the list of bins for the To Location if it is bin controlled
		if ($_entry_point==$modeltype.'_from_whlocation_id')
		{
			$from_bins = array();
			if ($from_location->isBinControlled())
			{
				$from_bins = $stitem->getBinList($_from_location_id);

				$this->view->set('from_bins',$from_bins);
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
			$output['from_whbin_id'] = array('data'=>$from_bins, 'is_array'=>is_array($from_bins));
		}

// ****************************************************************************
// Get the balance of the selected Item for the selected From Location/Bin
		if ($from_location->isBalanceEnabled())
		{
			$balance = $this->getBalance($_stitem_id, $_from_location_id, $_from_bin_id);
		}
		else
		{
			$balance = '-';
		}
		$this->view->set('balance', $balance);

		$output['balance'] = array('data'=>$balance, 'is_array'=>is_array($balance));

// ****************************************************************************
// get the associated 'To Location' values for the selected from location
		if ($_entry_point==$modeltype.'_from_whlocation_id')
		{
			$to_locations = $this->getToLocations($_from_location_id, $_whaction_id);

			$this->view->set('to_locations', $to_locations);
			$this->view->set('to_whlocation', $to_locations[$_to_location_id]);

			$_to_location_id = key($to_locations);

			$output['to_whlocation_id'] = array('data'=>$to_locations, 'is_array'=>is_array($to_locations));
			$_entry_point = $modeltype.'_to_whlocation_id';
		}

		$this->view->set('to_whlocation_id',$_to_location_id);

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
				$this->view->set('to_bins',$to_bins);
			}

			$output['to_whbin_id'] = array('data'=>$to_bins, 'is_array'=>is_array($to_bins));
		}

// ****************************************************************************
// Get list of transactions for the action and works order
		$sttransactions = new STTransactionCollection();

		$sh = new SearchHandler($sttransactions, false);

		$sh->addConstraint(new Constraint('process_name', '=', 'WO'));
		$sh->addConstraint(new Constraint('process_id', '=', $_work_order_id));
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

		$html = $this->view->fetch($this->getTemplateName('wo_issues_list'));

		$output['sttransactions'] = array('data'=>$html, 'is_array'=>is_array($html));

// ****************************************************************************
// Finally, if this is an ajax call, set the return data area
		if ($ajax)
		{
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}

	}

	public function getOrderLines($_order_id = '', $_orderline_id = '')
	{
	// used by ajax to get the Sales Order Lines on selecting a Sales Order

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['order_id'])) { $_order_id = $this->_data['order_id']; }
			if(!empty($this->_data['orderline_id'])) { $_orderline_id = $this->_data['orderline_id']; }
		}

		$orderline = DataObjectFactory::Factory('SOrderLine');

		$orderline->identifierField = array('line_number', 'description');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('order_id', '=', $_order_id));
		$cc->add(new Constraint('stitem_id', 'IS NOT', 'NULL'));

		// TODO: Check that order line has not already been assigned to another works order

		$cc1 = new ConstraintChain();

		$cc1->add(new Constraint('status', '=', $orderline->newStatus()));

		if (!empty($_orderline_id))
		{
			$cc1->add(new Constraint('id', '=', $_orderline_id), 'OR');
		}

		$cc->add($cc1);

		$list = $orderline->getAll($cc);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $list);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $list;
		}

	}

	public function getUomList($_id = '')
	{
	// used by ajax to get the UoM

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}

		$list = $this->_templateobject->getUomList($_id);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $list);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $list;
		}

	}

	public function displayLocations($whaction_id, &$errors = array()) {
//
//   1.  Gets a list of Stock Items to populate a select list
//       depending on the From Locations, if all From Locations
//       have balances
//
//   2.  Gets a list of From Locations, dependant on the supplied action_id
//
//       If the list of From Locations is a single location
//        - pass through the location_id and description
//        - if the location is bin_controlled
//                  get a list of bins for that location
//       else pass through the array of locations to populate a select list
//
//   3.  Gets a list of To Locations, dependant on the supplied action_id
//
//       If the list of To Locations is a single location
//        - pass through the location_id and description
//        - if the location is bin_controlled
//                  get a list of bins for that location
//       else pass through the array of locations to populate a select list
//
//	Ajax Operations
//
// TODO  On selecting an item, if the From and/or To Location has been selected
//       and either is bin_controlled, repopulate the list of bins that
//       contain the selected item
//
//       On selecting either a From or a To Location;
//       -if that location is bin_controlled, populate the list of bins;
//       -if the item is selected, then further constrain the selection by item
//
//       On selecting an Item and a From Location
//       -if that location is not bin_controlled and the location
//        has_balances then get the balance for the item at that location
//       -if that location is bin_controlled and the location
//        has_balances, on selecting the bin
//        then get the balance for the item at that location for that bin
//

// Get the list of Transfer From Locations for the supplied Action
		$from_locations = $this->getFromLocations($whaction_id);

		if (count($from_locations)==0)
		{
			$errors[] = 'No transfer rule defined';
			return;
		}

		$this->view->set('from_locations',$from_locations);

		$from_whlocation_ids=array_keys($from_locations);

// if all the locations are all balance enabled,
// get list of stock items for the locations
		$stock_items = array();

		$whlocation = DataObjectFactory::Factory('WHLocation');

		if ($whlocation->haveBalances($from_whlocation_ids))
		{
			$stock_items = STBalance::getStockList($from_whlocation_ids);
		}
		else
		{
			$stitem		 = DataObjectFactory::Factory('STItem');
			$stock_items = $stitem->getAll();
		}

		if ( count($stock_items)>0 )
		{
			$stitem_id = key($stock_items);
			$this->view->set('stock_item', $stock_items[$stitem_id]);
			$this->view->set('stitem_id', $stitem_id);
			$this->view->set('uom', $this->getUoM($stitem_id));
		}

// check the first from location
		$from_whlocation_id = key($from_locations);

		$this->view->set('from_whlocation_id', $from_whlocation_id);
		$this->view->set('from_whlocation', $from_locations[$from_whlocation_id]);

		$locations = DataObjectFactory::Factory('WHLocation');

		$location = $locations->load($from_whlocation_id);

		$from_bins = array();

		if ($location->isBinControlled())
		{
// The location has bins so get the list of bins
			$from_bins = STBalance::getBinList($stitem_id, $from_whlocation_id);
			$this->view->set('from_bins', $from_bins);
		}

		if ($location->isBalanceEnabled())
		{
			// Get the balance for the Stock/Location
			$chain = new ConstraintChain();

			$chain->add(new Constraint('stitem_id', '=', $stitem_id));

			$chain->add(new Constraint('whlocation_id', '=', $from_whlocation_id));

			if (!empty($from_bins) && count($from_bins)>0)
			{
				$chain->add(new Constraint('whbin_id', '=', key($from_bins)));
			}

			$balance = STBalance::getBalances($chain);

			$this->view->set('balance', $balance);
		}

// get the associated 'To Location' values for the first from location

		$to_locations = $this->getToLocations($from_whlocation_id, $whaction_id);

		$this->view->set('to_locations', $to_locations);

		$to_whlocation_id = key($to_locations);

		$this->view->set('to_whlocation_id', $to_whlocation_id);
		$this->view->set('to_whlocation', $to_locations[$to_whlocation_id]);

		$locations = DataObjectFactory::Factory('WHLocation');

		$location = $locations->load($to_whlocation_id);

		if ($location->isBinControlled())
		{
// The location has bins so get the list of bins
			$to_bins=$this->getBinList($to_whlocation_id);
			$this->view->set('to_bins', $to_bins);
		}

	}


    private function createAttachmentOutputFiles($entity_id) {

        $attachments = new EntityAttachmentOutputCollection;
        $sh = new SearchHandler($attachments, false);
        $sh->addConstraint(new Constraint('type', '=', 'application/pdf'));
        $sh->addConstraint(new Constraint('tag', '=', MFWorkorder::getAttachmentOutputsDefinition()['tag']));
        $sh->addConstraint(new Constraint('entity_id', '=', $entity_id));
        $sh->setOrderby('print_order', 'ASC');

        $attachments->load($sh);
        $attachment_paths = [];

        if (count($attachments) > 0) {
            foreach ($attachments as $attachment)
            {
                $file = DataObjectFactory::Factory('File');
                $file->load($attachment->id);

                $db = &DB::Instance();

                $content =$db->BlobDecode($file->file, $file->size); 

                $tpaths = $this->get_paths('123', 'pdf');
                $fhandle = fopen($tpaths['temp_file_path'], 'w');

                fwrite($fhandle, (string) $content);
                fclose($fhandle);

                $attachment_paths[] = $tpaths['temp_file_path'];
            }
        }

        return $attachment_paths;
    }

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((empty($base)?'works_orders':$base), $action);
	}
}

// End of MfworkordersController
