<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SinvoicelinesController extends Controller {

	protected $version='$Revision: 1.18 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('SInvoiceLine');
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		parent::index(new SInvoiceLineCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array_merge($this->_modules
									   ,array('controller'=>'SInvoiceLines'
											 ,'action'=>'new'
											 )
									   ),
					'tag'=>'new_SInvoiceLine'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		if (empty($this->_data['SInvoiceLine']['id'])) {
			$this->dataError();
			sendBack();
		}
		$flash = Flash::Instance();
		$sinvoiceline=$this->_uses[$this->modeltype];
		$sinvoiceline->load($this->_data['SInvoiceLine']['id']);
		$sinvoice=$sinvoiceline->invoice_detail;
		if ($sinvoiceline->isLoaded() && $sinvoiceline->delete()) {
			$flash->addMessage('Sales '.$sinvoice->getFormatted('transaction_type').' Line Deleted');
			if (isset($this->_data['dialog'])) {
				$link=array('modules'=>$this->_modules,
							'controller'=>'sinvoices',
							'action'=>'view',
							'other'=>array('id'=>$sinvoice->id)
				);
				$flash->save();
				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			} else {
				sendTo('sinvoices', 'view', $this->_modules, array('id'=>$sinvoice->id));
			}
		}
		$flash->addError('Error deleting Sales '.$sinvoice->getFormatted('transaction_type').' Line');
		$this->_data['id']=$this->_data['SInvoiceLine']['id'];
		$this->_data['invoice_id']=$this->_data['SInvoiceLine']['invoice_id'];
		$this->refresh();
	}

	public function _new() {

		$flash=Flash::Instance();

		parent::_new();

// Get the invoice Line Object - if loaded, this is an edit
		$sinvoiceline = $this->_uses[$this->modeltype];

		if (!$sinvoiceline->isLoaded()) {
			if (empty($this->_data['invoice_id'])) {
				$flash->addError('No Sales invoice supplied');
				sendBack();
			}
			$sinvoiceline->invoice_id=$this->_data['invoice_id'];
		}
		$sinvoice=new SInvoice();
		$sinvoice->load($sinvoiceline->invoice_id);

		$_slmaster_id=$sinvoice->slmaster_id;

		if (isset($this->_data[$this->modeltype])) {
		// We've had an error so refresh the page
			$_slmaster_id=$this->_data['SInvoice']['slmaster_id'];
			$sinvoiceline->line_number=$this->_data['SInvoiceLine']['line_number'];
			$_product_search=$this->_data['SInvoiceLine']['product_search'];
			if (!empty($this->_data['SInvoiceLine']['productline_id'])) {
				$_productline_id=$this->_data['SInvoiceLine']['productline_id'];
			} else {
				$_productline_id='';
			}
			$_glaccount_id=$this->_data['SInvoiceLine']['glaccount_id'];
		} elseif ($sinvoiceline->isLoaded()) {
			$_product_search=$sinvoiceline->description;
			$_productline_id=$sinvoiceline->productline_id;
			$_glaccount_id=$sinvoiceline->glaccount_id;
		} else {
			$sinvoiceline->due_despatch_date=$sinvoice->despatch_date;
			$sinvoiceline->due_delivery_date=$sinvoice->due_date;
		}
		$display_fields=$sinvoiceline->getDisplayFields();
		if (isset($display_fields['product_search'])) {
			if (empty($_product_search)) {
				$_product_search='None';
				$productline_options=array(''=>'None');
			} else {
				$productline_options=$this->getProductLines($_slmaster_id, $_product_search);
			}
		} else {
			$productline_options=$this->getProductLines($_slmaster_id);
		}
		if (empty($_productline_id)) {
			$_productline_id=key($productline_options);
		}
		$this->view->set('display_fields', $display_fields);
		$this->view->set('product_search', $_product_search);
		$this->view->set('productline_options', $productline_options);
		$data=$this->getProductLineData($_productline_id, $_slmaster_id);
		$this->view->set('stuom_options', $data['stuom_id']);
		$this->view->set('glaccount_options', $data['glaccount_id']);
		if (empty($_glaccount_id)) {
			$_glaccount_id=key($data['glaccount_id']);
		}
		$this->view->set('glcentre_options', $this->getCentre($_glaccount_id, $_productline_id));
		$this->view->set('taxrate_options', $data['tax_rate_id']);
		$this->view->set('sinvoice', $sinvoice);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$errors=array();

		$data=$this->_data['SInvoiceLine'];
		if (empty($data['invoice_id']))
		{
			$errors[]='invoice header not defined';
		}
		else
		{
			$sinvoice=DataObjectFactory::Factory('SInvoice');
			if (!$sinvoice->load($data['invoice_id']))
			{
				$errors[]='Cannot find invoice header';
			}
			elseif ($sinvoice->isLatest($this->_data['SInvoice'], $errors))
			{
				$sinvoiceline=SInvoiceLine::SInvoiceLineFactory($sinvoice, $data, $errors);
				if ($sinvoiceline && count($errors)==0)
				{
					if (!$sinvoiceline->save($sinvoice))
					{
						$errors[]='Failed to save Sales '.$sinvoice->getFormatted('transaction_type').' line';
					}
				}
			}
		}
		if(count($errors)==0) {
			$flash->addMessage('Sales '.$sinvoice->getFormatted('transaction_type').' Line Saved');
			if (isset($this->_data['saveAnother'])) {
				$other=array('invoice_id'=>$sinvoiceline->invoice_id);
				if (isset($this->_data['dialog'])) {
					$other+=array('dialog'=>'');
				}
				if (isset($this->_data['ajax'])) {
					$other+=array('ajax'=>'');
				}
				sendTo($this->name, 'new', $this->_modules, $other);
			} else {
				$action='view';
				$controller='sinvoices';
				$other=array('id'=>$sinvoiceline->invoice_id);
			}
			if (isset($this->_data['dialog'])) {
				$link=array('modules'=>$this->_modules,
							'controller'=>$controller,
							'action'=>$action,
							'other'=>$other
				);
				$flash->save();
				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			} else {
				sendTo($controller, $action, $this->_modules, $other);
			}
		} else {
			$flash->addErrors($errors);
			$this->_data['id']=$this->_data['SInvoiceLine']['id'];
			$this->_data['invoice_id']=$this->_data['SInvoiceLine']['invoice_id'];
			$this->refresh();
		}

	}

	public function CustomerServiceSummary () {

	}


// Private Functions
	private function buildProductLines($customer='', $productsearch='') {
// return the Product Lines list for a Customer
		$orderlines=array(''=>'None');
		$productlines=DataObjectFactory::Factory('SOProductline');
		if (!empty($customer)) {
			$orderlines+=$productlines->getCustomerLines($customer, $productsearch);
		}else {
			$orderlines+=$productlines->getNonSPecific($productsearch);
		}
		return $orderlines;
	}

	public function getAccount($_id='') {

		$account_list = array();
		$accounts = DataObjectFactory::Factory('GLAccount');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('control', '=', 'FALSE'));
		$account_list = $accounts->getAll($cc);
		return $account_list;

	}

	private function getProductLineData($_productline_id='', $_slmaster_id='') {
		$data=array();
		if (!empty($_productline_id)) {
			$productline = DataObjectFactory::Factory('SOProductline');
			$productline->load($_productline_id);
			if ($productline->isLoaded()) {
				$data['description']=$productline->description;
				$data['price']=$productline->getPrice('', '', $_slmaster_id);
				$data['stuom_id']=array($productline->product_detail->stuom_id=>$productline->product_detail->uom_name);
				$this->_templateobject->getField('stuom_id')->setnotnull();
				$account = DataObjectFactory::Factory('GLAccount');
				$account->load($productline->glaccount_id);
				$data['glaccount_id']=array($account->id=>$account->account.' - '.$account->description);
				$tax_rate = DataObjectFactory::Factory('TaxRate');
				$tax_rate->load($productline->product_detail->tax_rate_id);
				$data['tax_rate_id']=array($tax_rate->id=>$tax_rate->description);
			}
		} else {
			$data['description']=$this->getDefaultValue('SInvoiceLine', 'item_description', '');
			$data['price']=$this->getDefaultValue('SInvoiceLine', 'price', '0');
			$data['stuom_id']=$this->getUomList();
			$data['glaccount_id']=$this->getAccount();
			$data['tax_rate_id']=$this->getTaxRate();
		}
		return $data;
	}

	public function getTaxRate() {

		$tax_rate_list = array();
		$tax_rates = DataObjectFactory::Factory('TaxRate');
		$tax_rate_list=$tax_rates->getAll();
		return $tax_rate_list;
	}

	public function getUomList() {

		$uom_list=array();
		$uom=DataObjectFactory::Factory('STuom');
		$uom_list=array(''=>'None');
		$uom_list+=$uom->getAll();
		return $uom_list;

	}

// Ajax stuff!
	public function getProductLines($_slmaster_id='',$_product_search='',$_limit='') {
// Used by Ajax to return Product Lines list after selecting the Customer

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
			if(!empty($this->_data['product_search'])) { $_product_search=$this->_data['product_search']; }
			if(!empty($this->_data['limit'])) { $_limit=$this->_data['limit']; }
		}

		$productlist=$this->buildProductLines($_slmaster_id, $_product_search);
		if (!empty($_limit) && count($productlist)>$_limit) {
			$productlist=array(''=>'Refine Search - List > '.$_limit);
		} else {
			if (!empty($_product_search) && count($productlist)>1) {
				unset($productlist['']);
			}
		}

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$productlist);
			$this->setTemplateName('select_options');
		} else {
			return $productlist;
		}
	}

	public function getCentre($_glaccount_id='', $_productline_id='') {
// Used by Ajax to return Centre list after selecting the Product

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['glaccount_id'])) { $_glaccount_id=$this->_data['glaccount_id']; }
			if(!empty($this->_data['productline_id'])) { $_productline_id=$this->_data['productline_id']; }
		}

		$account_list = array();
		if ($_productline_id > 0) {
			$product = new SOProductline;
			$product->load($_productline_id);
			$centre = DataObjectFactory::Factory('GLCentre');
			$centre->load($product->glcentre_id);
			$centre_list[$centre->id] = $centre->cost_centre.' - '.$centre->description;
		} else {	
			$account = DataObjectFactory::Factory('GLAccount');
			$account->load($_glaccount_id);
			$centre_list = $account->getCentres();
		}

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$centre_list);
			$this->view->set('model', $this->_templateobject);
			$this->view->set('attribute', 'glcentre_id');
			$this->setTemplateName('select');
		} else {
			return $centre_list;
		}
	}

	public function getCentres($_glaccount_id='') {

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['glaccount_id'])) { $_id=$this->_data['glaccount_id']; }
		}

		// Used by Ajax to return Centre list after selecting the Account
		$account = DataObjectFactory::Factory('GLAccount');
		$account->load($_glaccount_id);
		$centres = $account->getCentres();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		} else {
			return $centres;
		}
	}


	/* consolodation functions */
	public function getLineData() {
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		// set vars
		$_productline_id=$this->_data['productline_id'];
		$_slmaster_id=$this->_data['slmaster_id'];

		$data=$this->getProductLineData($_productline_id, $_slmaster_id);

		$data['stuom_id']=$this->buildSelect('', 'stuom_id', $data['stuom_id']);
		$data['glaccount_id']=$this->buildSelect('', 'glaccount_id', $data['glaccount_id']);
		$data['tax_rate_id']=$this->buildSelect('', 'tax_rate_id', $data['tax_rate_id']);

		foreach ($data as $field=>$values) {
			$output[$field]=array('data'=>$values,'is_array'=>is_array($values));
		}

		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		$this->view->set('data',$output);
		$this->setTemplateName('ajax_multiple');

	}

	protected function getPageName($base=null, $action=null) {
		return parent::getPageName((!empty($base))?$base:'sales_invoice_line',$action);
	}

}

// End of SinvoicelinesController
