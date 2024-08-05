<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PorderlinesController extends Controller {

	protected $version = '$Revision: 1.28 $';

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('POrderLine');

		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{
		$this->view->set('clickaction', 'edit');

		parent::index(new POrderLineCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'tag'=>'new_POrderLine',
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											, 'action'=>'new'
											)
									   )
							)
				)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null)
	{
		if (empty($this->_data[$this->modeltype]['id']))
		{
			$this->dataError();
			sendBack();
		}

		$flash = Flash::Instance();

		$porderline = $this->_uses[$this->modeltype];

		$porderline->load($this->_data[$this->modeltype]['id']);

		$porder = $porderline->header;

		if (!$porder->isNew())
		{
			$flash->addError('Order status must be reset before the '.$porder->getFormatted('type').' Line can be deleted');
		}
		elseif ($porderline->isLoaded() && $porderline->delete())
		{
			$flash->addMessage($porder->getFormatted('type').' Line Deleted');

			if (isset($this->_data['dialog']))
			{
				$link=array('modules'=>$this->_modules,
							'controller'=>'porders',
							'action'=>'view',
							'other'=>array('id'=>$porderline->order_id)
				);

				$flash->save();

				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			}
			else
			{
				sendTo('porders', 'view', $this->_modules, array('id'=>$porderline->order_id));
			}
		}

		$flash->addError('Error deleting '.$porder->getFormatted('type').' Line');

		$this->_data['id']		 = $this->_data[$this->modeltype]['id'];
		$this->_data['order_id'] = $this->_data[$this->modeltype]['order_id'];

		$this->refresh();
	}

	/**
	 * Add a line to purchase against a Work Order Routing Outside Operation
	 */
	public function new_wopurchase()
	{

		$flash = Flash::Instance();

		parent::_new();

		// Get the Order Line Object - if loaded, this is an edit
		$porderline = $this->_uses[$this->modeltype];

		if (!$porderline->isLoaded())
		{
			if (empty($this->_data['order_id']))
			{
				$flash->addError('No Purchase Order supplied');
				sendBack();
			}

			$porderline->order_id	= $this->_data['order_id'];
			$porderline->status		= $porderline->newStatus();
		}

		$porder = DataObjectFactory::Factory('POrder');

		$porder->load($porderline->order_id);

		$_plmaster_id=$porder->plmaster_id;

		if (isset($this->_data[$this->modeltype]))
		{
		// We've had an error so refresh the page
			$_plmaster_id = $this->_data['POrder']['plmaster_id'];

			$porderline->line_number = $this->_data[$this->modeltype]['line_number'];

			//$_product_search = $this->_data[$this->modeltype]['product_search'];

			if (!empty($this->_data[$this->modeltype]['productline_id']))
			{
				$_productline_id = $this->_data[$this->modeltype]['productline_id'];
			}
			else
			{
				$_productline_id = '';
			}

			$_glaccount_id = $this->_data[$this->modeltype]['glaccount_id'];

		}
		elseif ($porderline->isLoaded())
		{
			$_product_search=$porderline->description;
			$_productline_id	= $porderline->productline_id;
			$_glaccount_id		= $porderline->glaccount_id;
		}
		else
		{
			$_product_search = 'None';

			$porderline->due_despatch_date = $porder->despatch_date;
			$porderline->due_delivery_date = $porder->due_date;
		}

		if ($porderline->lineAwaitingDelivery())
		{
			$porderlines = new POrderLineCollection();

			$porderlines->getAuthSummary($porder->id);

			if (EGS_USERNAME==$porder->checkAuthLimits($porderlines))
			{
				$this->view->set('amend_qty', true);
			}
		}

		$display_fields = $porderline->getDisplayFields();

		$options = $porderline::getWorkOrdersNeedingPurchase(['plmaster_id' => $_plmaster_id]);

		$productlines = new POProductlineCollection();
		foreach($options as $values) {
			$comp[] = $values['po_productline_header_id'];
		}

		$cc = new ConstraintChain();
		$cc->add(new Constraint('productline_header_id', 'in', '('.implode(',', $comp).')'));
		//$cc->add(new Constraint('productline_header_id', '=', $options[key($options)]['po_productline_header_id']));
		$cc->add(new Constraint('plmaster_id', '=', $_plmaster_id));
		$sh = new SearchHandler($productlines, false);
		$sh->addConstraint($cc);
		$productlines->load($sh);

		$op_options = [];
		$first_wo = $options[key($options)]['id'];
		foreach ($productlines as $p) {
			foreach ($options as $opt){
				if ($opt['po_productline_header_id'] == $p->productline_header_id && $opt['id'] == $first_wo) {
					$op_options[$p->id] = [
						'op_no' => $opt['op_no'],
						'op_id' => $opt['op_id'],
						'description' => $p->description
					];
					$this->view->set('default_description', "{$p->description}: {$opt['item_code']}, Work Order {$opt['wo_number']}");
					$this->view->set('default_price', $p->price);
					$this->view->set('net_value', number_format($options[key($options)]['order_qty'] * $p->price, 2));
					$this->view->set('default_due_date', date('d/m/Y', strtotime("+{$opt['lead_time']} weekdays")));
				}
			}
		}
		$productlines->rewind();

		uasort($op_options, function ($a, $b) { return strcmp((string) $a["op_no"], (string) $b["op_no"]); });

		if (empty($_productline_id)) {
			$_productline_id = $productlines->current()->id;
		}

		$this->view->set('display_fields', $display_fields);
		//$this->view->set('product_search', $_product_search);
		//$this->view->set('productline_options', $productline_options);

		$data = $this->getProductLineData($_productline_id);
		$this->view->set('stuom_options', $data['stuom_id']);
		$this->view->set('glaccount_options', $data['glaccount_id']);

		if (empty($_glaccount_id))
		{
			$_glaccount_id=key($data['glaccount_id']);
		}

		$wo_options = [];
		foreach ($options as $v) {
			$wo_options[$v['id']] = $v['workorder'];
		}

		$this->view->set('work_order_qty', $options[key($options)]['order_qty']);
		$this->view->set('glcentre_options', $this->getCentre($_glaccount_id, $_productline_id));
		$this->view->set('taxrate_options', $data['tax_rate_id']);
		$this->view->set('porder', $porder);
		$this->view->set('wo_options', $wo_options);
		$this->view->set('op_options', $op_options);
		$this->view->set('page_title', 'Work Order Purchase');
	}

	public function _new()
	{

		$flash = Flash::Instance();

		parent::_new();

// Get the Order Line Object - if loaded, this is an edit
		$porderline = $this->_uses[$this->modeltype];

		if (!$porderline->isLoaded())
		{
			if (empty($this->_data['order_id']))
			{
				$flash->addError('No Purchase Order supplied');
				sendBack();
			}

			$porderline->order_id	= $this->_data['order_id'];
			$porderline->status		= $porderline->newStatus();
		}

		$porder = DataObjectFactory::Factory('POrder');

		$porder->load($porderline->order_id);

		$_plmaster_id=$porder->plmaster_id;

		if (isset($this->_data[$this->modeltype]))
		{
		// We've had an error so refresh the page
			$_plmaster_id = $this->_data['POrder']['plmaster_id'];

			$porderline->line_number = $this->_data[$this->modeltype]['line_number'];

			$_product_search = $this->_data[$this->modeltype]['product_search'];

			if (!empty($this->_data[$this->modeltype]['productline_id']))
			{
				$_productline_id = $this->_data[$this->modeltype]['productline_id'];
			}
			else
			{
				$_productline_id = '';
			}

			$_glaccount_id = $this->_data[$this->modeltype]['glaccount_id'];

		}
		elseif ($porderline->isLoaded())
		{
//			$_product_search=$porderline->description;
			$_productline_id	= $porderline->productline_id;
			$_glaccount_id		= $porderline->glaccount_id;
		}
		else
		{
			$_product_search = 'None';

			$porderline->due_despatch_date = $porder->despatch_date;
			$porderline->due_delivery_date = $porder->due_date;
		}

		if ($porderline->lineAwaitingDelivery())
		{
			$porderlines = new POrderLineCollection();

			$porderlines->getAuthSummary($porder->id);

			if (EGS_USERNAME==$porder->checkAuthLimits($porderlines))
			{
				$this->view->set('amend_qty', true);
			}
		}

		$display_fields = $porderline->getDisplayFields();

		if (empty($_productline_id) && isset($display_fields['product_search']))
		{
			if ($_product_search=='None')
			{
				$productline_options = array(''=>'None');
			}
			else
			{
				$productline_options = $this->getProductLines($_plmaster_id, $_product_search);
			}
		}
		else
		{
			$productline_options = $this->getProductLines($_plmaster_id);
		}
		if (empty($_productline_id)) {
			$_productline_id=key($productline_options);
		}

		$this->view->set('display_fields', $display_fields);
		$this->view->set('product_search', $_product_search);
		$this->view->set('productline_options', $productline_options);

		$data = $this->getProductLineData($_productline_id);

		$this->view->set('stuom_options', $data['stuom_id']);
		$this->view->set('glaccount_options', $data['glaccount_id']);

		if (empty($_glaccount_id))
		{
			$_glaccount_id=key($data['glaccount_id']);
		}

		$this->view->set('glcentre_options', $this->getCentre($_glaccount_id, $_productline_id));
		$this->view->set('taxrate_options', $data['tax_rate_id']);
		$this->view->set('porder', $porder);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		$db = DB::Instance();

		$flash = Flash::Instance();

		$errors = array();

		$data = $this->_data[$this->modeltype];

		if (empty($data['order_id']))
		{
			$errors[] = 'Order header not defined';
		}
		else
		{
			$porder = DataObjectFactory::Factory('POrder');
			if (!$porder->load($data['order_id']))
			{
				$errors[] = 'Cannot find order header';
			}
		}

		$db->StartTrans();

		if ($porder->isLoaded() && $porder->isLatest($this->_data['POrder'], $errors))
		{
			if (isset($data['cancel_line']))
			{
				$data['status'] = $this->_templateobject->cancelStatus();

				if ($this->_templateobject->update($data['id']
													,array('status', 'glaccount_centre_id')
													,array($this->_templateobject->cancelStatus(), 'null'))
					&& $porder->save())
				{
					$flash->addMessage($porder->getFormatted('type').' Line cancelled');
				}
				else
				{
					$errors[] = 'Failed to cancel '.$porder->getFormatted('type').' Line';
				}
			}
			elseif (isset($data['complete_line']))
			{
				$porderline = $this->_uses[$this->modeltype];

				$porderline->load($data['id']);

				$poreceivedline = DataObjectFactory::Factory('POReceivedLine');

				if ($porderline->del_qty==$poreceivedline->invoicedQty($data['id']))
				{
					$fields	= array('status','glaccount_centre_id');
					$values	= array($this->_templateobject->invoiceStatus(), 'null');
				}
				else
				{
					$fields	= array('status');
					$values	= array($this->_templateobject->receivedStatus());
				}

				$fields[]	= 'os_qty';
				$values[]	= 0;

				if ($this->_templateobject->update($data['id']
													,$fields
													,$values)
					&& $porder->save())
				{
					$flash->addMessage($porder->getFormatted('type').' Line completed');
				}
				else
				{
					$errors[] = 'Failed to complete '.$porder->getFormatted('type').' Line';
				}
			}
			elseif ($data['status']==$this->_templateobject->newStatus()
					||$data['status']==$this->_templateobject->awaitingDeliveryStatus()
					||$data['status']==$this->_templateobject->partReceivedStatus()
					||$data['status']==$this->_templateobject->receivedStatus())
			{
				$porderline = POrderLine::porderlineFactory($porder, $data, $errors);

				if ($porderline && count($errors)==0)
				{
					if ($porder->due_date<$porderline->due_delivery_date)
					{
						$porder->due_date = $porderline->due_delivery_date;
					}

					if (!$porderline->save($porder, $errors))
					{
						$errors[] = 'Failed to save '.$porder->getFormatted('type').' Line';
					}
					else
					{
						$flash->addMessage($porder->getFormatted('type').' Line Saved');
					}
				}
			}
		}

		if(count($errors)==0)
		{
			$db->CompleteTrans();

			if (isset($this->_data['saveAnother']))
			{
				$other = array('order_id'=>$data['order_id']);

				if (isset($this->_data['dialog']))
				{
					$other+=array('dialog'=>'');
				}

				if (isset($this->_data['ajax']))
				{
					$other+=array('ajax'=>'');
				}

				if(isset($data['mf_workorders_id'])) {
					$available_wo_purchase = POrderLine::getWorkOrdersNeedingPurchase(['plmaster_id' => $porder->plmaster_id]);
					if (count($available_wo_purchase) != 0) {
						sendTo($this->name, 'new_wopurchase', $this->_modules, $other);
					} else {
						$flash->addMessage('No more Work Order purchases required for this supplier');
						unset($other);
						$action		= 'view';
						$controller	= 'porders';
						$other		= array('id'=>$data['order_id']);
					}
				} else {
					sendTo($this->name, 'new', $this->_modules, $other);
				}
			}
			else
			{
				$action		= 'view';
				$controller	= 'porders';
				$other		= array('id'=>$data['order_id']);
			}

			if (isset($this->_data['dialog']))
			{
				$link = array('modules'=>$this->_modules,
							 'controller'=>$controller,
							 'action'=>$action,
							 'other'=>$other
				);

				$flash->save();

				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			}
			else
			{
				sendTo($controller, $action, $this->_modules, $other);
			}
		}
		else
		{
			$db->FailTrans();

			$db->CompleteTrans();

			$flash->addErrors($errors);

			$this->_data['id']		 = $this->_data[$this->modeltype]['id'];
			$this->_data['order_id'] = $this->_data[$this->modeltype]['order_id'];

			$this->refresh();
		}

	}

	public function update_glcodes ()
	{
		$this->edit();
	}


// Private Functions
	private function buildProductLines($supplier='', $productsearch='')
	{
// return the Product Lines list for a Customer
		$orderlines = array(''=>'None');

		$productlines = DataObjectFactory::Factory('POProductline');

		if (!empty($supplier))
		{
			$orderlines += $productlines->getSupplierLines($supplier, $productsearch);
		}
		else
		{
			$orderlines += $productlines->getNonSPecific($productsearch);
		}

		return $orderlines;
	}

	private function getAccount()
	{

		$account_list = array();

		$accounts = DataObjectFactory::Factory('GLAccount');

		$cc = new ConstraintChain();

		$cc->add(new Constraint('control', '=', 'FALSE'));

		return $accounts->getAll($cc);

	}

	private function getProductLineData($_productline_id='')
	{
		$data = array();

		if (!empty($_productline_id))
		{
			$productline = DataObjectFactory::Factory('POProductline');

			$productline->load($_productline_id);

			if ($productline->isLoaded())
			{
				$data['description']	= $productline->description;
				$data['price']			= $productline->getPrice();
				$data['stuom_id']		= array($productline->product_detail->stuom_id=>$productline->product_detail->uom_name);

				$account = DataObjectFactory::Factory('GLAccount');
				$account->load($productline->glaccount_id);

				$data['glaccount_id']	= array($account->id=>$account->account.' - '.$account->description);

				$tax_rate = DataObjectFactory::Factory('TaxRate');
				$tax_rate->load($productline->product_detail->tax_rate_id);

				$data['tax_rate_id']	= array($tax_rate->id=>$tax_rate->description);
			}
		}
		else
		{
			$data['description']	= $this->getDefaultValue($this->modeltype, 'item_description', '');
			$data['price']			= $this->getDefaultValue($this->modeltype, 'price', '0');
			$data['stuom_id']		= $this->getUomList();
			$data['glaccount_id']	= $this->getAccount();
			$data['tax_rate_id']	= $this->getTaxRate();
		}
		return $data;
	}

	private function getTaxRate()
	{

		$tax_rate_list = array();

		$tax_rates = DataObjectFactory::Factory('TaxRate');

		$tax_rate_list = $tax_rates->getAll();

		ksort($tax_rate_list, SORT_NUMERIC);

		return $tax_rate_list;

	}

	private function getUomList()
	{

		$uom_list = array();

		$uom = DataObjectFactory::Factory('STUom');

		return $uom->getAll();

	}

// Ajax stuff!
	public function getProductLines($_plmaster_id = '', $_product_search = '', $_limit = '')
	{
// Used by Ajax to return Product Lines list after selecting the Supplier

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['plmaster_id'])) { $_plmaster_id = $this->_data['plmaster_id']; }
			if(!empty($this->_data['product_search'])) { $_product_search = $this->_data['product_search']; }
			if(!empty($this->_data['limit'])) { $_limit = $this->_data['limit']; }
		}

		$productlist = $this->buildProductLines($_plmaster_id, $_product_search);

		if (!empty($_limit) && count($productlist)>$_limit)
		{
			$productlist=array(''=>'Refine Search - List > '.$_limit);
		}
		else
		{
			if (!empty($_product_search) && count($productlist)>1)
			{
				unset($productlist['']);
			}
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$productlist);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $productlist;
		}
	}

	public function getCentres($_id = '')
	{

// Used by Ajax to return Centre list after selecting the Account
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}

		$account = DataObjectFactory::Factory('GLAccount');

		$account->load($_id);

		$centres = $account->getCentres();

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $centres;
		}

	}

	public function getCentre($_glaccount_id = '', $_productline_id = '') {

// Used by Ajax to return Centre list after selecting the Product

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['glaccount_id'])) { $_glaccount_id = $this->_data['glaccount_id']; }
			if(!empty($this->_data['productline_id'])) { $_productline_id = $this->_data['productline_id']; }
		}

		$account_list = array();

		if ($_productline_id > 0)
		{
			$product = DataObjectFactory::Factory('POProductline');
			$product->load($_productline_id);

			$centre = DataObjectFactory::Factory('GLCentre');
			$centre->load($product->glcentre_id);

			$centre_list[$centre->id] = $centre->cost_centre.' - '.$centre->description;
		}
		else
		{
			$account = DataObjectFactory::Factory('GLAccount');
			$account->load($_glaccount_id);
			$centre_list = $account->getCentres();
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$centre_list);
			$this->view->set('model', $this->_templateobject);
			$this->view->set('attribute', 'glcentre_id');
			$this->setTemplateName('select');
		}
		else
		{
			return $centre_list;
		}

	}

	/* consolodation functions */
	public function getLineData()
	{
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		// set vars
		$_productline_id = $this->_data['productline_id'];

		$data = $this->getProductLineData($_productline_id);

		$data['stuom_id']		= $this->buildSelect('', 'stuom_id', $data['stuom_id']);
		$data['glaccount_id']	= $this->buildSelect('', 'glaccount_id', $data['glaccount_id']);
		$data['tax_rate_id']	= $this->buildSelect('', 'tax_rate_id', $data['tax_rate_id']);

		// Add the lead time in weekdays to today's date
		if(isset($this->_data['op_id'])) {
			$op = new MFOperation();
			$op->load($this->_data['op_id']);
			$data['due_delivery_date'] = date('d/m/Y', strtotime("+{$op->lead_time} weekdays"));
			unset($data['description']);
		}

		foreach ($data as $field=>$values)
		{
			$output[$field] = array('data'=>$values, 'is_array'=>is_array($values));
		}

		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		$this->view->set('data',$output);
		$this->setTemplateName('ajax_multiple');

	}

	/**
	 * Return a response containing updated Work Order operation/productline information
	 * 
	 * Called via XHR when a work order is selected for a new work order purchase PO line
	 * 
	 * @see PordelinesControllers::new_wopurchase
	 */
	public function getWorkOrderOperationLines()
	{

		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		$mfworkorder_id = $this->_data['mfworkorder_id'];
		$plmaster_id = $this->_data['plmaster_id'];

		$work_order_ops = POrderLine::getWorkOrdersNeedingPurchase(['plmaster_id' => $plmaster_id, 'workorder_id' => $mfworkorder_id]);

		foreach($work_order_ops as $values) {
			$plid[] = $values['po_productline_header_id'];
		}

		$productlines = new POProductlineCollection();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('productline_header_id', 'in', '('.implode(',', $plid).')'));
		$cc->add(new Constraint('plmaster_id', '=', $plmaster_id));
		$sh = new SearchHandler($productlines, false);
		$sh->addConstraint($cc);
		$productlines->load($sh);

		$op_options = [];
		foreach ($work_order_ops as $r) {
			foreach ($productlines as $p) {
				if ($r['po_productline_header_id'] == $p->productline_header_id) {
					$op_options[$p->id] = [
						'op_no' => $r['op_no'],
						'op_id' => $r['op_id'],
						'description' => $p->description
					];
				}
			}
		}

		//$data = $this->getProductLineData(key(op_options));
		foreach ($productlines as $p) {
			if ($p->id == key($op_options)) {
				$productline = $p;
			}
		}

		$data['description']	= "{$productline->description}: {$work_order_ops[0]['item_code']}, Work Order {$work_order_ops[0]['wo_number']}";
		$data['price']			= $productline->getPrice();
		$data['stuom_id']		= array($productline->product_detail->stuom_id=>$productline->product_detail->uom_name);

		$account = DataObjectFactory::Factory('GLAccount');
		$account->load($productline->glaccount_id);
		$data['glaccount_id']	= array($account->id=>$account->account.' - '.$account->description);

		$tax_rate = DataObjectFactory::Factory('TaxRate');
		$tax_rate->load($productline->product_detail->tax_rate_id);
		$data['tax_rate_id']	= array($tax_rate->id=>$tax_rate->description);

		$data['stuom_id']		= $this->buildSelect('', 'stuom_id', $data['stuom_id']);
		$data['glaccount_id']	= $this->buildSelect('', 'glaccount_id', $data['glaccount_id']);
		$data['tax_rate_id']	= $this->buildSelect('', 'tax_rate_id', $data['tax_rate_id']);

		foreach ($data as $field=>$values)
		{
			$output[$field] = array('data'=>$values, 'is_array'=>is_array($values));
		}

		$first_op = $op_options[key($op_options)]['op_id'];
		$template = '<select id="POrderLine_productline_id" data-field="productline_id" name="POrderLine[productline_id]" class="nonone">';
		foreach ($op_options as $pl => $op) {
			$template .= "<option value=\"{$pl}\" data-opid=\"{$op['op_id']}\">{$op['op_no']} - {$op['description']}</option>";
		}
		$template .= '</select><input type="hidden" id="POrderLine_mf_operations_id" data-field="mf_operations_id" name="POrderLine[mf_operations_id]" value="{$first_op}">';

		$output['productline_id'] = [
			'data' => $template,
			'is_array' => false
		];

		$output['revised_qty'] = [
			'data' => $work_order_ops[0]['order_qty'],
			'is_array' => false
		];

		$this->view->set('data', $output);
		$this->setTemplateName('ajax_multiple');
	}

	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((!empty($base))?$base:'purchase_order_line', $action);
	}

}

// End of PorderlinesController
