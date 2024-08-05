<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class soproductlinesController extends printController
{

	protected $version='$Revision: 1.78 $';

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('SOProductline');

		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$s_data=array();

// Set context from calling module
		if (isset($this->_data['slmaster_id']))
		{
			$s_data['slmaster_id']=$this->_data['slmaster_id'];
		}

		if (isset($this->_data['status']))
		{
			$s_data['status']=$this->_data['status'];
		}

		if (!isset($this->_data['Search']) || isset($this->_data['Search']['clear']))
		{
			$s_data['start_date/end_date']=date(DATE_FORMAT);
		}

		$this->setSearch('productlinesSearch', 'customerDefault', $s_data);

		parent::index(new SOProductlineCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$actions = array();

		$actions['new']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlineheaders'
								 ,'action'=>'new'
								 ),
					'tag'=>'new_product'
				);

		$actions['all_products']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlineheaders'
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

		$actions['unused']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'unused'
								 ),
					'tag'=>'unused lines'
				);

		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('clickaction','view_so_product');
		$this->view->set('linkfield','productline_header_id');
		$this->view->set('linkvaluefield','productline_header_id');

	}

	public function unused()
	{

		$errors = array();

		$s_data=array();

		if (!isset($this->_data['session_data_key']))
		{
			// session is not set so this is the first entry
			$this->_data['session_data_key'] = get_class($this->_templateobject).'-delete-unused';
			$page_data = new SessionData($this->_data['session_data_key']);
			$page_data->registerPageData(array('select'));
		}
		else
		{
			$page_data = new SessionData($this->_data['session_data_key']);
		}

// Update page data if paging
		if (isset($this->_data[$this->modeltype])
			&& (isset($this->_data['page']) || isset($this->_data['orderby'])))
		{
			foreach ($this->_data[$this->modeltype] as $id=>$fields)
			{
				if ($fields['select'] == 'on')
				{
					$page_data->updatePageData($id, $fields, $errors);
				}
				else
				{
					$page_data->deletePageData($id);
				}
			}
		}

		$data = $page_data->getPageData();

// Set context from calling module

		$this->setSearch('productlinesSearch', 'customerDefault', $s_data);

		$productlines = new SOProductlineCollection($this->_templateobject);

		$sh = $this->setSearchHandler($productlines);

		if (count($errors) > 0)
		{
			$flash = Flash::Instance();
			$flash->addErrors($errors);
			sendback();
		}

		$sh->addConstraint(new Constraint('not exists', '', '(select 1 from so_lines where productline_id=so_productlines_overview.id)'));
		$sh->addConstraint(new Constraint('not exists', '', '(select 1 from si_lines where productline_id=so_productlines_overview.id)'));

		parent::index($productlines, $sh);

		if (isset($this->_data['select_all']))
		{
			if (count($data) > 0)
			{
				$page_data->clearPageData();
			}
			else
			{
				$productline = DataObjectFactory::Factory($this->modeltype, 'so_productlines_overview');
				// We already have the constraints from above in the search handler
				$cc = $sh->constraints;
				// Just need to get the ids
				foreach ($productline->getAll($cc) as $id=>$productline)
				{
					$page_data->updatePageData($id, array('select'=>'on'), $errors);
				}
			}
			$data = $page_data->getPageData();
		}

		$sidebar = new SidebarController($this->view);

		$actions = array();

		$actions['all_products']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlineheaders'
								 ,'action'=>'index'
								 ),
					'tag'=>'view all products'
				);

		$actions['all_lines']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlines'
								 ,'action'=>'index'
								 ),
					'tag'=>'view all lines'
				);

		$sidebar->addList(
			'Actions',
			$actions
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

		$this->view->set('collection', $productlines);
		$this->view->set('clickaction','view_so_product');
		$this->view->set('linkfield','productline_header_id');
		$this->view->set('linkvaluefield','productline_header_id');

		// set the generic variables for the paging_select template
		$this->view->set('session_data_key', $this->_data['session_data_key']);
		$this->view->set('selected', $data);
		$this->view->set('count_selected', count($data));

		if (count($data) > 0)
		{
			$this->view->set('select_all_text','Clear All');
		}
		else
		{
			$this->view->set('select_all_text','Select All');
		}

		$this->view->set('submit_text','Delete Selected');
		$this->view->set('form_action','delete_selected');

		$this->setTemplateName('paging_select');

	}

	public function delete_selected()
	{

		if (!$this->checkParams('session_data_key'))
		{
			$this->dataError();
			sendBack();
		}

		$flash=Flash::Instance();

		$db = DB::Instance();

		$db->StartTrans();

		$errors=array();

		$session_data_key = $this->_data['session_data_key'];

		$page_data = new SessionData($session_data_key);

		foreach ($this->_data[$this->modeltype] as $id=>$fields)
		{
			if (!isset($fields['select']) && isset($fields['_checkbox_exists_select']))
			{
				$page_data->deletePageData($id);
			}
			else
			{
				$page_data->updatePageData($id, $fields, $errors);
			}
		}

		$data = $page_data->getPageData();

		// Could do with a progress bar here as the number of records could be large
		$delete_count = 0;

		if (count($data) > 0)
		{

			$progressBar = new Progressbar('soproductline_delete_unused');

			$callback = function($fields, $id) use (&$delete_count) {

				if ($fields['select'] == 'on')
				{
					$productline = DataObjectFactory::Factory('SOProductLine');
					$productline->load($id);

					if (!$productline->isLoaded() || !$productline->delete($id, $errors))
					{
						return FALSE;
					}

					$delete_count++;
				}

			};

			if ($progressBar->process($data, $callback)===FALSE)
			{
				$errors[] = 'Failed to delete product line';
			}

		}
		else
		{
			$flash->addWarning('Nothing selected to delete');
		}

		// reset timeout to 30 seconds to allow time to redisplay the page
		// hopefully, it will be quicker than this!
		set_time_limit(30);

		if (count($errors)>0)
		{
			$flash->addErrors($errors);
			$flash->addError($db->ErrorMsg());
			$db->FailTrans();
			$db->CompleteTrans();
			$this->refresh();

		}
		else
		{
			$page_data->clear();
			$db->CompleteTrans();
			$flash->addMessage($delete_count.' record'.get_plural_string($delete_count).' archived successfully');
			sendTo($this->name, 'unused', $this->_modules);
		}

	}

	/**
	 * Price change index page
	 *
	 * @return void
	 */
	public function price_uplift()
	{
		$this->view->set('clickaction', 'edit');

		$s_data=array();

		if (isset($this->_data['Search'])
		|| $this->_data['saveform']=='Recalculate'
		|| (empty($this->_data['Search'])
			&& !isset($this->_data['page'])
			&& !isset($this->_data['orderby'])
			&& !isset($_SESSION['price_uplift_errors']))) {
				unset($_SESSION['price_uplift']);
		}

// Set context from calling module
		if (isset($this->_data['productline_header_id'])) {
			$productline_header_id = $s_data['productline_header_id'] = $this->_data['productline_header_id'];
		}
		elseif (isset($this->_data['Search']['productline_header_id']))
		{
			$productline_header_id = $this->_data['Search']['productline_header_id'];
		}
		else
		{
			$productline_header_id = '';
		}

		if (!empty($productline_header_id))
		{
			$product = DataObjectFactory::Factory('SOProductlineHeader');
			$product->load($productline_header_id);
			$this->view->set('page_title', $this->getPageName() . ' for '.$product->description);
		}
		if (isset($this->_data['slmaster_id'])) {
			$s_data['slmaster_id']=$this->_data['slmaster_id'];
		}
		if (isset($this->_data['status'])) {
			$s_data['status']=$this->_data['status'];
		}
		if (!empty($this->_data[$this->modeltype]['percent'])) {
			$percent=$this->_data[$this->modeltype]['percent'];
		} elseif (isset($_SESSION['price_uplift_params']['percent'])) {
			$percent=$_SESSION['price_uplift_params']['percent'];
		} elseif (isset($_POST[$this->modeltype]['percent'])) {
			$percent=$_POST[$this->modeltype]['percent'];
		}
		if (empty($_POST[$this->modeltype]['percent'])) {
			$_POST[$this->modeltype]['percent']=$percent;
		}

		if (empty($this->_data[$this->modeltype]['fixed_price'])) {
			$fixed_price = 0;
		} else {
			$fixed_price = $this->_data[$this->modeltype]['fixed_price'];
		}
		$this->view->set('fixed_price',$fixed_price);

		if ($this->_data['saveform']=='Recalculate' && empty($percent) && $percent!==0 && (empty($fixed_price) && $fixed_price == 0)) {
			$flash=Flash::Instance();
			$flash->addError('Enter a percentage or fixed price value.');
		}
		if (empty($this->_data[$this->modeltype]['effective_date'])) {
			$effective_date='';
		} else {
			$effective_date=$this->_data[$this->modeltype]['effective_date'];
		}
		$this->view->set('effective_date',$effective_date);

		if (!empty($this->_data[$this->modeltype]['decimals'])) {
			$decimals=$this->_data[$this->modeltype]['decimals'];
		} elseif (isset($_SESSION['price_uplift_params']['decimals'])) {
			$decimals=$_SESSION['price_uplift_params']['decimals'];
		} else {
			$decimals=2;
		}
		$this->view->set('decimals',$decimals);

		$_SESSION['price_uplift_params']['percent']=$percent;
		$_SESSION['price_uplift_params']['decimals']=$decimals;
		$_SESSION['price_uplift_params']['fixed_price']=$fixed_price;

		$this->view->set('soproductline',$this->_templateobject);
		$this->setSearch('productlinesSearch', 'customerPriceUplift', $s_data);

		$collection=new SOProductlineCollection($this->_templateobject);
		$sh=$this->setSearchHandler($collection);
		parent::index($collection, $sh);

		$selected=empty($_SESSION['price_uplift'][$collection->cur_page])?array():$_SESSION['price_uplift'][$collection->cur_page];

		foreach ($collection as $detail) {
			if (!isset($selected[$detail->id])) {
				$selected[$detail->id]['select']='true';
				if ($fixed_price == 0) {
					$selected[$detail->id]['new_price']=bcmul(round(($detail->getGrossPrice()*(100+$percent)/100),$decimals),1,$decimals);
				} else {
					$selected[$detail->id]['new_price']=round($fixed_price, $decimals);
				}

				if (isset($_POST[$this->modeltype][$detail->id]['new_price']))
				{
					$_POST[$this->modeltype][$detail->id]=$selected[$detail->id];
				}
			}
		}
		$_SESSION['price_uplift'][$collection->cur_page]=$selected;

		$this->view->set('selected', $selected);

		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'new'
								 ),
					'tag'=>'new_product_line'
				),
				'plan'=>array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'viewByItems'
								 ),
					'tag'=>'view_supply/demand'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	/**
	 * Adjust price selections
	 * 
	 * Called by javascript.
	 *
	 * @return void
	 */
	public function adjust_price_uplift ()
	{

		if (!isset($this->_data['current_page'])) {
			return;
		} else {
			$page=$this->_data['current_page'];
		}
		$selected=empty($_SESSION['price_uplift'][$page])?array():$_SESSION['price_uplift'][$page];

		$params=array();
		if (isset($this->_data['id'])) {
			if (isset($this->_data['new_price'])) {
				$selected[$this->_data['id']]['new_price']=$this->_data['new_price'];
			}
			if (isset($this->_data['select'])) {
				$selected[$this->_data['id']]['select']=$this->_data['select'];
			}
		}
		$_SESSION['price_uplift'][$page]=$selected;
		exit;
	}

	
	
	public function save_price_uplift ()
	{
		$flash=Flash::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		$errors=array();
		$warnings=array();

		if (empty($this->_data[$this->modeltype]['percent'])) {
			$percent=0;
		} else {
			$percent=$this->_data[$this->modeltype]['percent'];
		}

		if (empty($this->_data[$this->modeltype]['decimals'])) {
			$errors['decimals']='You must specify the number of decimal places required';
		} else {
			$decimals=$this->_data[$this->modeltype]['decimals'];
		}

		if (empty($this->_data[$this->modeltype]['effective_date'])) {
			$errors['effective_date']='You must specify the effective date for the price change';
		} else {
			$start_date=fix_date($this->_data[$this->modeltype]['effective_date']);
			$end_date=fix_date(date(DATE_FORMAT,strtotime('-1 days',strtotime((string) $start_date))));
			if ($start_date<fix_date(date(DATE_FORMAT))) {
				$flash->addWarning('Start Date is before today', 'start_date');
			}
		}

		if (empty($this->_data[$this->modeltype]['fixed_price'])) {
			$fixed_price = 0;
		} else {
			$fixed_price = $this->_data[$this->modeltype]['fixed_price'];
		}

		if (count($errors)==0) {
			$count=0;

			$this->setSearch('productlinesSearch', 'customerPriceUplift');

			$collection=new SOProductlineCollection();
			$sh=$this->setSearchHandler($collection);
			$sh->setFields('*');
			parent::index($collection, $sh);
			$lastpage=$collection->num_pages;
			$_SESSION['price_uplift_params']['search_id']=$this->search->getValue('search_id');
			$_SESSION['price_uplift_params']['percent']=$percent;
			$_SESSION['price_uplift_params']['decimals']=$decimals;
			$_SESSION['price_uplift_params']['fixed_price']=$fixed_price;
			$_SESSION['price_uplift_params']['price_uplift_total_records']=$collection->num_records;
			$_SESSION['price_uplift_params']['price_uplift_progress_count']=0;
			$_SESSION['price_uplift_params']['price_uplift_updated_count']=0;
			$_SESSION['price_uplift_params']['price_uplift_end_date']=$end_date;
			$_SESSION['price_uplift_params']['price_uplift_start_date']=$start_date;
			$_SESSION['price_uplift_params']['price_uplift_last_page']=$lastpage;

			$selected=empty($_SESSION['price_uplift'][$collection->cur_page])?array():$_SESSION['price_uplift'][$collection->cur_page];
			foreach ($collection as $productline) {
				if (!isset($selected[$productline->id])) {
					$selected[$productline->id]['select']='true';
					if ($fixed_price == 0) {
						$selected[$productline->id]['new_price'] = bcmul(round(($productline->getGrossPrice()*(100+$percent)/100),$decimals),1,$decimals);
					} else {
						$selected[$productline->id]['new_price'] = round($fixed_price, $decimals);
					}
					
				}
			}
			$_SESSION['price_uplift'][$collection->cur_page]=$selected;
		}
		if (count($errors)>0) {
			$flash->addErrors($errors);
			echo json_encode(false);
		} else {
			echo json_encode($lastpage);
		}
		$flash->save();
		exit;
	}

	/**
	 * Updates prices on each 'page'
	 * 
	 * Called by javascript
	 *
	 * @return void
	 */
	public function save_price_uplift_pages ()
	{
		$flash=Flash::Instance();

		$this->setSearch('productlinesSearch', 'customerPriceUplift');

		$page=$this->_data['page'];

		$this->_data['search_id']=$_SESSION['price_uplift_params']['search_id'];

		$collection=new SOProductlineCollection();

		$sh=$this->setSearchHandler($collection);
		$sh->setFields('*');

		parent::index($collection, $sh);

		$selected=empty($_SESSION['price_uplift'][$page])?array():$_SESSION['price_uplift'][$page];

		$percent=$_SESSION['price_uplift_params']['percent'];
		$decimals=$_SESSION['price_uplift_params']['decimals'];
		$fixed_price=$_SESSION['price_uplift_params']['fixed_price'];

		foreach ($collection as $productline)
		{
			if (!isset($selected[$productline->id]))
			{
				$selected[$productline->id]['select']='true';
				if (empty($fixed_price) || $fixed_price == 0) {
					$selected[$productline->id]['new_price'] = bcmul(round(($productline->getGrossPrice()*(100+$percent)/100),$decimals),1,$decimals);
				} else {
					$selected[$productline->id]['new_price'] = round($fixed_price, $decimals);
				}
			}
		}

		$_SESSION['price_uplift'][$collection->cur_page]=$selected;

		echo json_encode(array('data_page'=>$page, 'cur_page'=>$collection->cur_page, 'selected'=>$selected));
		exit;
	}

	public function end_price_uplift()
	{

		$db = DB::Instance();

		$flash = Flash::Instance();

		if ($flash->hasErrors())
		{
			$_SESSION['price_uplift_errors'] = true;

			$db->FailTrans();

			$db->CompleteTrans();

			$this->refresh();
		}
		else
		{
			$count = $_SESSION['price_uplift_params']['price_uplift_updated_count'];

			if ($count>0)
			{
				// Clear any messages left over from the previous uplift.
				$flash->clear();
				$text = ($count==1)?'price':'prices';
				$flash->addMessage($count.' new '.$text.' saved OK');
			}

			$db->CompleteTrans();
			unset($_SESSION['price_uplift']);
			unset($_SESSION['price_uplift_params']);

			sendTo($this->name, 'price_uplift', $this->_modules);
		}

	}


	/**
	 * End productline and add new
	 * 
	 * Called by javascript. Handles a price uplift by ending
	 * the old productline and adding a new one with the
	 * updated price.
	 *
	 * @return void
	 */
	public function update_prices ()
	{
		$flash=Flash::instance();

		$errors=array();

		$warnings=array();

		$db = DB::Instance();

		$start_date=$_SESSION['price_uplift_params']['price_uplift_start_date'];

		$end_date=$_SESSION['price_uplift_params']['price_uplift_end_date'];

		$selected=empty($_SESSION['price_uplift'][$this->_data['page']])?array():$_SESSION['price_uplift'][$this->_data['page']];

		$count=0;

		foreach ($selected as $id=>$detail)
		{
			$db->StartTrans();

			if ($detail['select']=='true')
			{
				$productline = DataObjectFactory::Factory('SOProductLine');

				$productline->load($id);

				if (!$productline->isLoaded())
				{
					$errors[$id]='Failed to find product details '.$id;
				}
				elseif ($detail['new_price'] <= 0)
				{
					$errors[$id] = "{$productline->description}, not updated. Negative or zero price.";
				}
				elseif ($productline->price==$detail['new_price'])
				{
					$warnings[$id] = "{$productline->description}, not updated. Price has not changed.";
				}
				else
				{
					$productline->end_date = $end_date;

					if (!$productline->save())
					{
						$errors[$id] = 'Failed to close off old price ref:'.$id.' - '.$db->ErrorMsg();
					}
					else
					{
						$test = $productline->autoHandle($productline->idField);

						if($test !== false)
						{
							$productline->id 		 = $test;
							$productline->price		 = $detail['new_price'];
							$productline->start_date = $start_date;
							$productline->end_date	 = null;
							$productline->created	 = fix_date(date(DATE_FORMAT));
							$productline->createdby	 = EGS_USERNAME;

							if (!$productline->save())
							{
								$errors[$id] = 'Failed to save new price ref:'.$id.' - '.$db->ErrorMsg();
							}
							else
							{
								$count++;
							}
						}
						else
						{
							$errors[] = 'Error getting identifier for new price';
						}

					}
				}

				unset($productline);
			}

			$db->CompleteTrans();

			$_SESSION['price_uplift_params']['price_uplift_progress_count']++;
		}

		if (count($errors)>0)
		{
			$flash->addErrors($errors);
		}

		if (count($warnings)>0)
		{
			$flash->addWarnings($warnings);
		}

		$flash->save();

		$_SESSION['price_uplift_params']['price_uplift_updated_count']+=$count;

		echo json_encode(array('updated_count'=>$count, 'warnings'=>$warnings, 'errors'=>$errors));
		exit;
	}

	public function _new()
	{

		// need to store the ajax flag in a different variable and the unset the original
		// this is to prevent any functions that are further called from returning the wrong datatype
		$ajax=isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		parent::_new();

		$product=$this->_uses[$this->modeltype];

		$product_header = DataObjectFactory::Factory('SOProductlineHeader');

		if (isset($this->_data['productline_header_id']))
		{
			$product_header->load($this->_data['productline_header_id']);
		}
		elseif (isset($this->_data['stitem_id']))
		{
			$product_header->loadBy('stitem_id', $this->_data['stitem_id']);

			if ($product_header->isLoaded())
			{
//				echo 'data<pre>'.print_r($_GET, true).'</pre><br>';
				$_POST['productline_header_id'] = $this->_data['productline_header_id'] = $product_header->id;
			}
//			else
//			{
//				echo 'Could not load header for item '.$this->_data['stitem_id'].'<br>';
//			}

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
			$default_customer		= $product->slmaster_id;
		}
		else
		{
			$default_customer		= '';
		}

		$data = $this->getHeaderData($product->productline_header_id, $default_customer);

		if (!$product->isLoaded())
		{
			$this->view->set('price', $data['price']['data']);
			$this->view->set('discount', $data['discount']['data']);
			$default_glaccount_id	= $data['glaccount_id']['data'];
		}

		$this->view->set('gl_account', $default_glaccount_id);
		$this->view->set('gl_centre', $product_header->glcentre_id);
		$this->view->set('gl_centres', $this->getCentres($default_glaccount_id));

		$this->view->set('gross_price', $this->_templateobject->getGrossPrice());

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) :void
	{
		$flash=Flash::Instance();
		$errors=array();

		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}

		$productline_data = $this->_data[$this->modeltype];

		$header = DataObjectFactory::Factory('SOProductlineHeader');

		$header->load($productline_data['productline_header_id']);

		if (!$header->isLoaded())
		{
			$errors[] = 'Error loading header';
		}

// If the customer is specified, the price type must match to the customer
		if (!empty($productline_data['slmaster_id']))
		{
			// Load the customer
			$customer = DataObjectFactory::Factory('SLCustomer');

			$customer->load($productline_data['slmaster_id']);

			if (!$customer->isLoaded())
			{
				$errors[] = 'Error loading customer';
			}
			else
			{
				if ((!is_null($customer->so_price_type_id)
					&& (empty($productline_data['so_price_type_id']) || $productline_data['so_price_type_id'] != $customer->so_price_type_id))
				||	(is_null($customer->so_price_type_id) && !empty($productline_data['so_price_type_id'])))
				{
					$errors[] = 'Price Type invalid for this Customer';
				}
			}
		}

// If no description is entered, use the supplier product code
		if (empty($productline_data['slmaster_id']) &&
			!empty($productline_data['customer_product_code']))
		{
			$productline_data['customer_product_code']='';
			$flash->addMessage('Customer Code ignored as no Customer selected');
		}

// If there is no description, use the supplier code
		if (empty($productline_data['description']) &&
			!empty($productline_data['customer_product_code']))
		{
			$productline_data['description']=$productline_data['customer_product_code'];
		}

// If there is no description, use the header description
		if (empty($productline_data['description']))
		{
			$productline_data['description']=$header->description;
		}

// Price is either entered directly or comes from the item
		if (empty($productline_data['price'])) {
			$errors[] = 'You must enter a price';
		}

// If there is no description, then no customer or header has been selected
		if (empty($productline_data['description']))
		{
			$errors[] = 'You must select a customer &/or enter a description';
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

		if (empty($productline_data['slmaster_id']))
		{
			$cc->add(New Constraint('slmaster_id', 'is', 'NULL'));
		}
		else
		{
			$cc->add(New Constraint('slmaster_id', '=', $productline_data['slmaster_id']));
		}

		if (empty($productline_data['so_price_type_id']))
		{
			$cc->add(New Constraint('so_price_type_id', 'is', 'NULL'));
		}
		else
		{
			$cc->add(New Constraint('so_price_type_id', '=', $productline_data['so_price_type_id']));
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

		$productline = DataObjectFactory::Factory('SOProductLine');
		$overlap = $productline->getAll($cc);

		if (count($overlap) > 0)
		{
			$errors[] = 'Current product price already exists';
		}

		if (count($errors)==0)
		{
			if(parent::save($this->modeltype, $productline_data, $errors))
			{
				if (isset($this->_data['saveform']))
				{
					sendTo($this->name, 'view_so_product', $this->_modules, array('productline_header_id' => $productline_data['productline_header_id']));
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

		if (isset($this->_data['SOProductline']['id']))
		{
			$this->_data['id']=$this->_data['SOProductline']['id'];
			$this->_data['productline_header_id']=$this->_data['SOProductline']['productline_header_id'];
		}

		$this->refresh();

	}

	public function view_so_product()
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

		$this->setSearch('productlinesSearch', 'soheaderLines', $s_data);

// Load the Product Header
		$product = DataObjectFactory::Factory('SOProductlineHeader');

		$product->load($productline_header_id);

		$this->view->set('SOProductlineHeader', $product);

// Load the associated lines
		parent::index(new SOProductlineCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['view_all_products']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlineheaders'
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

		$sidebarlist['view']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'view_so_product'
								 ,'productline_header_id'=>$productline_header_id
								 ),
					'tag'=>'view'
				);

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
								 ,'controller'=>'soproductlineheaders'
								 ,'action'=>'view_orders'
								 ,'id'=>$productline_header_id
								 ),
					'tag'=>'view orders'
				);

		$sidebarlist['invoices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductlineheaders'
								 ,'action'=>'view_invoices'
								 ,'id'=>$productline_header_id
								 ),
					'tag'=>'view invoices'
				);

		$sidebarlist['prices']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'price_uplift'
								 ,'productline_header_id'=>$productline_header_id
								 ),
					'tag'=>'amend prices'
				);

		$sidebar->addList(
			'this Product',
			$sidebarlist
		);

		$sidebarlist = array();

		if (SelectorCollection::TypeDetailsExist($this->modeltype))
		{
		$sidebarlist['items']=array(
					'link'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductselectors'
								 ,'action'=>'used_by'
								 ,'target_id'=>$productline_header_id
								 ),
					'new'=>array('modules'=>$this->_modules
								 ,'controller'=>'soproductselectors'
								 ,'action'=>'select_items'
								 ,'target_id'=>$productline_header_id
								 ),
					'tag'=>'used by'
				);
		}

		$sidebar->addList(
			'related_items',
			$sidebarlist
		);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

		$this->view->set('linkmodule', $this->module);
		$this->view->set('linkcontroller', 'soproductlineheaders');

	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName(($base)?$base:'SO_product_lines',$action);
	}


/* Ajax Functions */
	public function getCentres($_glaccount_id='')
	{
// Used by Ajax to return Centre list after selecting the Account

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['glaccount_id'])) { $_glaccount_id=$this->_data['glaccount_id']; }
		}

		$account = DataObjectFactory::Factory('GLAccount');

		$account->load($_glaccount_id);

		$centre_list = $account->getCentres();

		if(isset($this->_data['ajax']))
		{
			$output['glcentre_id'] = array('data'=>$centre_list,'is_array'=>true);
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $centre_list;
		}

	}

	public function getCurrency($_slmaster_id='')
	{
// Used by Ajax to return Currency after selecting the Supplier

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		$customer = DataObjectFactory::Factory('SLCustomer');

		$customer->load($_slmaster_id);

		$currency='';

		if ($customer)
		{
			$currency=$customer->currency_id;
		}

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$currency);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $currency;
		}

	}

	function getPriceDiscount($_prod_group_id = '', $_stitem_id = '', $_slmaster_id = '')
	{

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['prod_group_id'])) { $_prod_group_id=$this->_data['prod_group_id']; }
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		$discount = $this->_templateobject->getPriceDiscount($_prod_group_id, $_slmaster_id);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$discount);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $discount;
		}

	}

	function getProductLines($_slmaster_id='', $_productsearch='', $_so_price_type_id='', $_limit='')
	{

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
			if(!empty($this->_data['product_search'])) { $_productsearch=$this->_data['product_search']; }
			if(!empty($this->_data['so_price_type_id'])) { $_so_price_type_id=$this->_data['so_price_type_id']; }
			if(!empty($this->_data['limit'])) { $_limit=$this->_data['limit']; }
		}

		$productlines = DataObjectFactory::Factory('SOProductline');

		if (!empty($_slmaster_id))
		{
			$productlist=$productlines->getCustomerLines($_slmaster_id, $_productsearch);
		}
		else
		{
			$productlist=$productlines->getNonSPecific($_productsearch, $_so_price_type_id);
		}

		if (!empty($_limit) && count($productlist)>$_limit)
		{
			$productlist=array(''=>'Refine Search - List > '.$_limit);
		}
		elseif (count($productlist)==0)
		{
			$productlist=array(''=>'None')+$productlist;
		}
		elseif (!empty($_productsearch) && count($productlist)>1)
		{
			$productlist=array(''=>'Select from list')+$productlist;
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

	function get_price($_productline_id='', $_slmaster_id='')
	{

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['productline_id'])) { $_productline_id=$this->_data['productline_id']; }
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		if (empty($_productline_id))
		{
			$currency='';
			$product_price='';
			$discount_percent='';
			$discount_value='';
			$net_price='';
			$vat='';
			$gross='';
		}
		else
		{
			$productline = DataObjectFactory::Factory('SOProductline');

			$productline->load($_productline_id);

			if ($productline->isLoaded())
			{
				$currency=$productline->currency;
				$product_price=$productline->getGrossPrice();
				$discount_percent=$productline->getPriceDiscount('', $_slmaster_id);
				$net_price=$productline->getPrice('', '', $_slmaster_id);
				$discount_value=bcsub((string) $product_price, (string) $net_price);
				$sales_stock = $productline->product_detail->item_detail->pickableBalance();

				if (empty($_slmaster_id))
				{
					$_slmaster_id=$productline->slmaster_id;
				}
				if (!empty($_slmaster_id))
				{
					$customer = DataObjectFactory::Factory('SLCustomer');

					$customer->load($_slmaster_id);

					$tax_status_id=$customer->tax_status_id;
				}
				else
				{
					$tax_status_id='';
				}
				$tax_percentage=calc_tax_percentage($productline->product_detail->tax_rate_id,$tax_status_id,$net_price);
				$vat = bcadd(round($net_price*$tax_percentage,2),0);
				$gross=bcadd((string) $net_price, $vat);
			}
			else
			{
				$net_price='Not Found';
			}
		}

		$output['currency']=array('data'=>$currency,'is_array'=>false);
		$output['product_price']=array('data'=>$product_price,'is_array'=>false);
		$output['discount_percent']=array('data'=>$discount_percent,'is_array'=>false);
		$output['discount_value']=array('data'=>$discount_value,'is_array'=>false);
		$output['net_price']=array('data'=>$net_price,'is_array'=>false);
		$output['vat']=array('data'=>$vat,'is_array'=>false);
		$output['gross']=array('data'=>$gross,'is_array'=>false);
		$output['sales_stock'] = [
			'data' => $sales_stock,
			'is_array' => false
		];

		if(isset($this->_data['ajax']))
		{
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return $output;
		}

	}

	function get_price_type($_slmaster_id='')
	{

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		$customer = DataObjectFactory::Factory('SLCustomer');

		$customer->load($_slmaster_id);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$customer->so_price_type_id);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $customer->so_price_type_id;
		}

	}

	/* consolodation functions */
	function getHeaderData ($_header_id = '', $_slmaster_id = '')
	{

		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		if($ajax)
		{
			if(!empty($this->_data['productline_header_id'])) { $_header_id=$this->_data['productline_header_id']; }
			if(!empty($this->_data['slmaster_id'])) { $_slmaster_id=$this->_data['slmaster_id']; }
		}

		if (!empty($_header_id))
		{
			$header = $this->loadHeader($_header_id);
			$this->view->set('SOProductlineHeader', $header);

			$output['description']=array('data'=>$header->description,'is_array'=>false);
			$output['discount']=array('data'=>$this->getPriceDiscount($header->prod_group_id, $header->stitem_id, $_slmaster_id),'is_array'=>false);
			$output['price']=array('data'=>$this->_templateobject->getPrice($header->prod_group_id, $header->stitem_id, $_slmaster_id),'is_array'=>false);
			$output['glaccount_id']=array('data'=>$header->glaccount_id,'is_array'=>false);
			$output['start_date']=array('data'=>un_fix_date($header->start_date),'is_array'=>false);
			$output['end_date']=array('data'=>un_fix_date($header->end_date),'is_array'=>false);

		}

		if($ajax)
		{
			$html = $this->view->fetch($this->getTemplateName('header_data'));
			$output['header_data']=array('data'=>$html,'is_array'=>false);
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
		$product=$this->_uses[$this->modeltype];

		if (!$product->isLoaded())
		{
			$product->productline_header_id = $_header_id;
		}
		return $product->product_detail;
	}

}

// End of soproductlinesController
