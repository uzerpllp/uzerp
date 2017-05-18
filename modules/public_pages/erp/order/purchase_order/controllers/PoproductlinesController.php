<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PoproductlinesController extends printController {

	protected $version = '$Revision: 1.60 $';

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('POProductline');
		
		$this->uses($this->_templateobject);
	
	}

	public function index()
	{

		$s_data = array();

// Set context from calling module
		if (isset($this->_data['plmaster_id']))
		{
			$s_data['plmaster_id'] = $this->_data['plmaster_id'];
		}
		
		if (isset($this->_data['status']))
		{
			$s_data['status'] = $this->_data['status'];
		}
		
		if (!isset($this->_data['Search']) || isset($this->_data['Search']['clear']))
		{
			$s_data['start_date/end_date']=date(DATE_FORMAT);
		}
		
		$this->setSearch('productlinesSearch', 'supplierDefault', $s_data);

		parent::index(new POProductlineCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$actions = array();

		$actions['new']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'poproductlineheaders'
								 ,'action'=>'new'
								 ),
					'tag'=>'new_product'
				);
		
		$actions['all_products']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'poproductlineheaders'
								 ,'action'=>'index'
								 ),
					'tag'=>'view all products'
				);

		$actions['new_line']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ),
					'tag'=>'new_product_line'
				);
		
		$sidebar->addList(
			'Actions',
			$actions
		);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('clickaction','view_po_product');
		$this->view->set('linkfield','productline_header_id');
		$this->view->set('linkvaluefield','productline_header_id');

	}

	public function _new()
	{

		// need to store the ajax flag in a different variable and the unset the original
		// this is to prevent any functions that are further called from returning the wrong datatype
		$ajax=isset($this->_data['ajax']);
		unset($this->_data['ajax']);
		
		parent::_new();
		
		$product=$this->_uses[$this->modeltype];
		
		$product_header = DataObjectFactory::Factory('POProductlineHeader');
		
		if (isset($this->_data['productline_header_id']))
		{
			$product_header->load($this->_data['productline_header_id']);
		}
		elseif (isset($this->_data['stitem_id']))
		{
			$product_header->loadBy('stitem_id', $this->_data['stitem_id']);
			
			if ($product_header->isLoaded())
			{
				$this->_data['productline_header_id'] = $product_header->id;
			}
			
		}
		
		if (!$product->isLoaded())
		{
			
			if (isset($this->_data['productline_header_id']) && $product_header->isLoaded())
			{
				$product->productline_header_id = $this->_data['productline_header_id'];
			}
			else
			{
				
				$headers = $product_header->getAll();
				$this->view->set('headers', $headers);
				
				$product->productline_header_id = key($headers);
			}
		}
		else
		{
			$this->_data['productline_header_id'] = $product->productline_header_id;
		}
		
		$glaccount = DataObjectFactory::Factory('GLAccount');
		
		$gl_accounts = $glaccount->nonControlAccounts();
		
		$this->view->set('gl_accounts',$gl_accounts);
		
		if ($product->isLoaded())
		{
			$default_glaccount_id	= $product->glaccount_id;
			$default_supplier		= $product->plmaster_id;
		}
		else
		{
			$default_supplier		= '';
		}

		$data = $this->getHeaderData($product->productline_header_id, $default_supplier);
		
		if (!$product->isLoaded())
		{
			$this->view->set('price', $data['price']['data']);
			$this->view->set('discount', $data['discount']['data']);
			$default_glaccount_id	= $data['glaccount_id']['data'];
		}
		
		$this->view->set('gl_account', $default_glaccount_id);
		$this->view->set('gl_centres', $this->getCentres($default_glaccount_id));
				
	}
	
	public function save()
	{
		
		$flash=Flash::Instance();
		$errors=array();
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$productline_data = $this->_data[$this->modeltype];
		
		$header = DataObjectFactory::Factory('POProductlineHeader');
		
		$header->load($productline_data['productline_header_id']);
		
		if (!$header->isLoaded())
		{
			$errors[] = 'Error loading header';
		}

		// If no description is entered, use the supplier product code		
		if (empty($productline_data['plmaster_id']) &&
			!empty($productline_data['supplier_product_code']))
		{
			$productline_data['supplier_product_code'] = '';
			$flash->addMessage('Supplier Code ignored as no Supplier selected');
		}
// If there is no description, use the supplier code		
		if (empty($productline_data['description']) &&
			!empty($productline_data['supplier_product_code']))
		{
			$productline_data['description'] = $productline_data['supplier_product_code'];
		}
// If there is no description, use the description item code description
		if (empty($productline_data['description']) &&
			!empty($productline_data['stitem_id']))
		{
			$stitem = DataObjectFactory::Factory('STItem');
			
			$stitem->load($productline_data['stitem_id']);
			
			$productline_data['description'] = $stitem->getIdentifierValue();
		}
// Price is either entered directly or comes from the item		
		if (empty($productline_data['price']) &&
			empty($productline_data['stitem_id']))
		{
			$errors[] = 'You must enter a price or select an item';
		}
// If there is no description, then no supplier or item has been selected
		if (empty($productline_data['description']))
		{
			$errors[] = 'You must select an item &/or a supplier &/or enter a description';
		}
		
// If the header has an end date, make sure the line end date is set
// and not later than the header
		if (!is_null($header->end_date)
			&& (empty($productline_data['end_date']) || fix_date($productline_data['end_date'])>$header->end_date))
		{
			$productline_data['end_date'] = un_fix_date($header->end_date);
			$flash->addMessage('End date set to header end date');
		}

// Check for overlapping lines
		$cc = new ConstraintChain();
		
		$cc->add(New Constraint('productline_header_id', '=', $productline_data['productline_header_id']));
		
		if (!empty($productline_data['id']))
		{
			$cc->add(New Constraint('id', '!=', $productline_data['id']));
		}
		
		if (empty($productline_data['plmaster_id']))
		{
			$cc->add(New Constraint('plmaster_id', 'is', 'NULL'));
		}
		else
		{
			$cc->add(New Constraint('plmaster_id', '=', $productline_data['plmaster_id']));
		}
				
		$db = DB::Instance();
		
		$cc1 = new ConstraintChain();
		$cc1->add(New Constraint('start_date', 'between', $db->qstr(fix_date($productline_data['start_date'])).' and '.$db->qstr((empty($productline_data['end_date'])?fix_date(date(DATE_FORMAT)):fix_date($productline_data['end_date'])))));
		
		$cc2 = new ConstraintChain();
		$cc2->add(New Constraint('start_date', '<', fix_date($productline_data['start_date'])));
		
		$cc3 = new ConstraintChain();
		$cc3->add(New Constraint('end_date', '>=', fix_date($productline_data['start_date'])));
		$cc3->add(New Constraint('end_date', 'is', 'NULL'), 'OR');
		
		$cc2->add($cc3);
		$cc1->add($cc2, 'OR');
		$cc->add($cc1);
		
		$productline = DataObjectFactory::Factory('POProductLine');
		$overlap = $productline->getAll($cc);
		
		if (count($overlap) > 0)
		{
			$errors[] = 'Current product price already exists';
		}
		
		if (count($errors)==0)
		{
			if(parent::save('POProductline', $productline_data, $errors))
			{
				if (isset($this->_data['saveform']))
				{
					sendTo($this->name, 'view_po_product', $this->_modules, array('productline_header_id' => $productline_data['productline_header_id']));
				}
				else
				{
					sendTo($this->name, 'new', $this->_modules, array('productline_header_id'=>$productline_data['productline_header_id']));
				}
			}
			else
			{
				$errors[]='Failed to save Product Line';
			}
		}
		
		$flash->addErrors($errors);
		
		if (isset($this->_data['POProductline']['id']))
		{
			$this->_data['id']=$this->_data['POProductline']['id'];
		}
		
		$this->refresh();

	}

	public function view_po_product()
	{
		
		if (!isset($this->_data['productline_header_id']) && !isset($this->_data['Search']['productline_header_id'])) {
			$this->DataError();
			sendBack();
		}
		
		$this->view->set('clickaction', 'edit');

		$s_data=array();

// Set context from calling module
		if (!isset($this->_data['Search']) || isset($this->_data['Search']['clear']))
		{
			$s_data['start_date/end_date']=date(DATE_FORMAT);
			$s_data['productline_header_id']=$this->_data['productline_header_id'];
		}
				
		if (isset($this->_data['productline_header_id']))
		{
			$productline_header_id = $this->_data['productline_header_id'];
		}
		elseif (isset($this->_data['Search']['productline_header_id']))
		{
			$productline_header_id = $this->_data['Search']['productline_header_id'];
		}
		
		$this->setSearch('productlinesSearch', 'poheaderLines', $s_data);

// Load the Product Header
		$product = DataObjectFactory::Factory('POProductlineHeader');
		
		$product->load($productline_header_id);
		
		$this->view->set('POProductlineHeader', $product);

// Load the associated lines
		parent::index(new POProductlineCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['view_all_products']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'poproductlineheaders'
								 ,'action'=>'index'
								 ),
					'tag'=>'View All Products'
				);
		
		$sidebarlist['view_all_lines']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 ),
					'tag'=>'View All Product Lines'
				);
		
				$sidebar->addList(
			'All Products',
			$sidebarlist
		);
				
		$sidebarlist = array();
		
		$sidebarlist['new']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ,'productline_header_id'=>$productline_header_id
								 ),
					'tag'=>'new price'
				);
				
		$sidebarlist['orders']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'poproductlineheaders'
								 ,'action'=>'view_orders'
								 ,'id'=>$productline_header_id
								 ),
					'tag'=>'view orders'
				);
		
		$sidebarlist['invoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'poproductlineheaders'
								 ,'action'=>'view_invoices'
								 ,'id'=>$productline_header_id
								 ),
					'tag'=>'view invoices'
				);
		
		$sidebar->addList(
			'this Product',
			$sidebarlist
		);
				
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
				
		$this->view->set('linkmodule', $this->module);
		$this->view->set('linkcontroller', 'poproductlineheaders');
	
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName(($base)?$base:'PO_product_lines', $action);
	}
	
/* Ajax Functions */
	public function getCentres($_glaccount_id = '')
	{
// Used by Ajax to return Centre list after selecting the Account

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['glaccount_id'])) { $_glaccount_id = $this->_data['glaccount_id']; }
		}
		
		$account = DataObjectFactory::Factory('GLAccount');
		
		$account->load($_glaccount_id);
		
		$centre_list = $account->getCentres();
		
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
	
	public function getCurrency($_id = '')
	{
// Used by Ajax to return Currency after selecting the Supplier
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		$supplier = DataObjectFactory::Factory('PLSupplier');
		
		$supplier->load($_id);
		
		$currency = '';
		
		if ($supplier)
		{
			$currency = $supplier->currency_id;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $currency);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $currency;
		}
	}
	
	public function getDefaultProductAccount()
	{
		
		$account = $this->_templateobject->getDefaultProductAccount();

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $account);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $account;
		}
		
	}
	
	public function getDefaultProductCentre()
	{
		
		$centre = $this->_templateobject->getDefaultProductCentre();
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $centre);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $centre;
		}
		
	}
	
	function getProductGroups($_id='')
	{
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['id'])) { $_id = $this->_data['id']; }
		}
		
		$groups = $this->_templateobject->getProductGroups($_id);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options',$groups);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $groups;
		}
		
	}

	/* consolodation functions */
	function getHeaderData ($_header_id = '', $_plmaster_id = '')
	{
		
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);
		
		if($ajax)
		{
			if(!empty($this->_data['productline_header_id'])) { $_header_id = $this->_data['productline_header_id']; }
			if(!empty($this->_data['plmaster_id'])) { $_plmaster_id = $this->_data['plmaster_id']; }
		}
		
		if (!empty($_header_id))
		{
			$header = $this->loadHeader($_header_id);
			$this->view->set('POProductlineHeader', $header);
			
			$output['description']	= array('data'=>$header->description, 'is_array'=>false);
			$output['price']		= array('data'=>$this->_templateobject->getPrice($header->prod_group_id, $header->stitem_id, $_plmaster_id), 'is_array'=>false);
			$output['glaccount_id']	= array('data'=>$header->glaccount_id, 'is_array'=>false);
			$output['start_date']	= array('data'=>un_fix_date($header->start_date), 'is_array'=>false);
			$output['end_date']		= array('data'=>un_fix_date($header->end_date), 'is_array'=>false);
			
		}
	
		if($ajax)
		{
			$html = $this->view->fetch($this->getTemplateName('header_data'));
			$output['header_data'] = array('data'=>$html, 'is_array'=>false);
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $output;
		}
	
	}
	
	/* private functions */
	private function loadHeader($_header_id = '')
	{
		$product = $this->_uses[$this->modeltype];
		
		if (!$product->isLoaded())
		{
			$product->productline_header_id = $_header_id;
		}
		
		return $product->product_detail;
	}
	
}

// End of poproductlinesController
