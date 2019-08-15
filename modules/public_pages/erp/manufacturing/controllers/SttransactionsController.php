<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SttransactionsController extends ManufacturingController {

	protected $_templateobject;
	protected $version='$Revision: 1.36 $';
	
	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STTransaction();
		$this->uses($this->_templateobject);
	}

	public function index(){
		$errors = array();
		$s_data = array();
		
		// Set context from calling module
		if (isset($this->_data['status'])) {
			$s_data['status'] = $this->_data['status'];
		}
		if (isset($this->_data['stitem_id'])) {
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}
		if (isset($this->_data['whlocation_id'])) {
			$s_data['whlocation_id'] = $this->_data['whlocation_id'];
		}
		
		if (empty($this->_data['from'])) {
			$s_data['created']['from']=date(DATE_FORMAT,strtotime('-7 days'));
		} else {
			$s_data['created']['from']=un_fix_date($this->_data['from']);
		}
		if (empty($this->_data['to'])) {
			$s_data['created']['to']=date(DATE_FORMAT);
		} else {
			$s_data['created']['to']=un_fix_date($this->_data['to']);
		}
			
		$this->setSearch('sttransactionsSearch', 'useDefault', $s_data);

		$sttransactions = new STTransactionCollection($this->_templateobject);

		if (!isset($this->_data['orderby'])
			&& !isset($this->_data['page'])) {
			$sh = $this->setSearchHandler($sttransactions);
			$cc = new ConstraintChain;
			$cc->add(new Constraint('error_qty', '<', 0));
			$cc->add(new Constraint('qty', '<', 0), 'OR');
			$sh->addConstraintChain($cc);
			$sh->setOrderby('created', 'DESC');
			parent::index($sttransactions, $sh);
		} else {
			parent::index($sttransactions);
		}
		$this->view->set('clickaction','view');

	}

	public function view_balance(){
		$errors = array();
		$s_data = array();
		
		// Set context from calling module
		if (isset($this->_data['status'])) {
			$s_data['status'] = $this->_data['status'];
		}
		if (isset($this->_data['stitem_id'])) {
			$s_data['stitem_id'] = $this->_data['stitem_id'];
		}
		if (isset($this->_data['whlocation_id'])) {
			$s_data['whlocation_id'] = $this->_data['whlocation_id'];
		}
		
		$s_data['created']['from']=date(DATE_FORMAT,strtotime('-7 days'));
		$s_data['created']['to']=date(DATE_FORMAT);
		
		$this->setSearch('sttransactionsSearch', 'useDefault', $s_data);

		$sttransactions = new STTransactionCollection($this->_templateobject);

		parent::index($sttransactions);
		
		$this->view->set('clickaction','view');
		
	}

	public function _new() {

		if (!$this->CheckParams('whaction_id')) {
			sendBack();
		}
		$_whaction_id=$this->_data['whaction_id'];
		
		$errors=array();
//		$this->displayLocations($whaction_id, $errors);
		$this->getTransferDetails($_whaction_id);
		if (count($errors)>0) {
			$flash=Flash::Instance();
			$flash->addErrors($errors);
			sendTo('WHActions'
					,'actionsMenu'
					,$this->_modules);
		}
		
		$whaction = new WHAction();
		$whaction->load($_whaction_id);
		$this->view->set('label',$whaction->label);
		$this->view->set('sub_title',$whaction->description);
		$this->view->set('page_title',$this->getPageName($whaction->action_name,''));
		parent::_new();

	}
	
	public function save() {
		
		parent::save_transactions();
         
	}
	
	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		$id = $this->_data['id'];
		$this->view->set('transaction',$transaction);

		$link='';
		switch ($transaction->process_name) {
			case 'D':
				$despatchline=new SOdespatchline();
				$despatchline->loadBy('despatch_number', $transaction->process_id);
				if ($despatchline->isLoaded()) {
					$link='"module":"despatch"
						  ,"controller":"sodespatchlines"
						  ,"action":"view"
						  ,"id":"'.$despatchline->id.'"';
				}
				break;
			case 'GR':
				$poreceivedline=new POReceivedLine();
				$poreceivedline->loadBy('gr_number', $transaction->process_id);
				if ($poreceivedline->isLoaded()) {
					$link='"module":"goodsreceived"
						  ,"controller":"poreceivedlines"
						  ,"action":"view"
						  ,"id":"'.$poreceivedline->id.'"';
				}
				break;
			case 'SC':
			case 'SI':
				$link='"module":"sales_invoicing"
					  ,"controller":"sinvoices"
					  ,"action":"view"
					  ,"id":"'.$transaction->process_id.'"';
				break;
			case 'SO':
				$link='"module":"sales_order"
					  ,"controller":"sorders"
					  ,"action":"view"
					  ,"id":"'.$transaction->process_id.'"';
				break;
			case 'WO':
				$link='"module":"manufacturing"
					  ,"controller":"mfworkorders"
					  ,"action":"view"
					  ,"id":"'.$transaction->process_id.'"';
				break;
		}
		$this->view->set('linkto',$link);
		
		$sidebar=new SidebarController($this->view);
		$sidebarlist=array();
		if ($transaction->status == 'E') {
			$sidebarlist['backflusherrors'] = array(
						'tag' => 'All Backflush Errors',
						'link' => array('modules'=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'=>'index'
									   ,'status'=>'E'
									   )
						);
		}
		$sidebarlist['alltransactions'] = array(
					'tag' => 'All Transactions',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'index'
								   )
						);
		$sidebarlist['transactions_for_location'] = array(
					'tag' => 'transactions_for_location',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'index'
								   ,'whlocation_id'=>$transaction->whlocation_id
								   ,'from'=>fix_date(date(DATE_FORMAT, strtotime($transaction->created)))
								   ,'to'=>fix_date(date(DATE_FORMAT, strtotime("+1 day", strtotime($transaction->created))))
								   )
						);
		$sidebarlist['transactions_for_item'] = array(
					'tag' => 'transactions_for_item',
					'link' => array('modules'=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'=>'index'
								   ,'stitem_id'=>$transaction->stitem_id
								   ,'from'=>fix_date(date(DATE_FORMAT, strtotime($transaction->created)))
								   ,'to'=>fix_date(date(DATE_FORMAT, strtotime("+1 day", strtotime($transaction->created))))
								   )
						);
						
		$sidebar->addList('Show',$sidebarlist);
		
		if ($transaction->status == 'E') {
			$sidebarlist=array();
			$sidebarlist['resolve']= array(
						'tag' => 'Resolve Transaction',
						'link' => array('modules'=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'=>'changeStatus'
									   ,'id'=>$id
									   ,'status'=>'R'
									   )
						);
	
			$sidebarlist['cancel']= array(
						'tag' => 'Cancel Transaction',
						'link' => array('modules'=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'=>'changeStatus'
									   ,'id'=>$id
									   ,'status'=>'C'
									   )
						);
			$sidebar->addList('Actions',$sidebarlist);
		}
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function changeStatus() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		if (isset($this->_data['status'])) {
			$transaction->status = $this->_data['status'];
			$this->view->set('status', $transaction->status);
		}
		$this->view->set('transaction', $transaction);
		parent::_new();
	}
	
	public function saveStatus() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		$flash = Flash::Instance();
		$errors = array();
		$data = $this->_data[$this->modeltype];
		$twin_transaction = null;
		if ($data['revised_qty']<0) {
			$errors[]='Quantity must be a positive number';
		}
		if ($transaction && $transaction->current_balance()<$data['revised_qty']) {
			$errors[]='There is insufficient balance to resolve this error';
		}
		if ($transaction && count($errors)==0) {
			$twin_transaction = $transaction->getTwinTransaction();
		} else {
			$flash->addErrors($errors);
			$this->_data['id'] = $data['id'];
			$this->_data['status'] = $data['status'];
			$this->changeStatus();
			$this->_templateName = $this->getTemplateName('changestatus');
			return;
		}
		$success = true;
		$db = DB::Instance();
		$db->StartTrans();
		$transaction->status = $data['status'];
		$transaction->remarks = $data['remarks'];
		if ($transaction->save($errors) === false) {
			$errors[] = 'Error saving transaction status';
			$success = false;
			$db->FailTrans();
		} elseif ($twin_transaction) {
			$twin_transaction->status = $data['status'];
			$twin_transaction->remarks = $data['remarks'];
			if ($twin_transaction->save($errors) === false) {
				$errors[] = 'Error saving transaction status';
				$success = false;
				$db->FailTrans();
			}
		}
		if (($success) && ($data['status'] == 'R')) {
			$new_transactions = array($transaction);
			if ($twin_transaction) {
				$new_transactions[] = $twin_transaction;
			}
			$transfer_rule = new WHTransferrule;
			$transfer_id = $transfer_rule->getTransferId();
			$skip_fields = array('id', 'transfer_id', 'created', 'balance', 'status');
			foreach ($new_transactions as $key => $new_transaction) {
				$new_data = array();
				$fields = $new_transaction->toArray();
				foreach ($fields as $field_name => $field) {
					if (in_array($field_name, $skip_fields)) {
						continue;
					}
					$new_data[$field_name] = $field['value'];
				}
				$new_data['transfer_id'] = $transfer_id->transfer_id;
				$new_data['balance'] = 0;
				$new_data['status'] = 'O';
				$new_data['qty'] = ($new_data['error_qty']<0)?$data['revised_qty']*-1:$data['revised_qty'];
				$new_data['error_qty'] = 0;
				$new_transactions[$key] = STTransaction::Factory($new_data, $errors, $this->modeltype);
				if ($new_transactions[$key] !== false) {
					continue;
				}
				$errors[] = 'Error transferring stock';
				$success = false;
				break;
			}
			if ($success) {
				foreach ($new_transactions as $new_transaction) {
					$new_transaction->save($errors);
					if (count($errors) == 0) {
						continue;
					}
					$errors[] = 'Error transferring stock';
					$success = false;
					$db->FailTrans();
					break;
				}
			}
		}
		$db->CompleteTrans();
		if ($success) {
			$flash->addMessage('Transaction status changed successfully');
			sendTo($this->name
					,'view'
					,$this->_modules
					,array('id' => $data['id']));
		} else {
			$flash->addErrors($errors);
			$this->_data['id'] = $data['id'];
			$this->_data['status'] = $data['status'];
			$this->changeStatus();
			$this->_templateName = $this->getTemplateName('changestatus');
		}
	}
	
	function printAction () {
		if ($this->_data['printaction']=='printTransactions') {
			$this->printtype=array('csv'=>'CSV'
								  ,'xml'=>'XML');
		}
		parent::printAction();
	}

	public function getBalance($_stitem_id = '', $_location_id = '', $_bin_id = '')
	{
		
		// Function called by Ajax Request to return balance for selected item, location, bin
		
		if (isset($this->_data['ajax']))
		{
			if (!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
			if (!empty($this->_data['whlocation_id'])) { $_location_id=$this->_data['whlocation_id']; }
			if (!empty($this->_data['whbin_id'])) { $_bin_id=$this->_data['whbin_id']; }
		}
		
		$balance = new STBalance();
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $_stitem_id));
		$cc->add(new Constraint('whlocation_id', '=', $_location_id));
		
		if (!empty($_bin_id) && $_bin_id != "null")
		{
			$cc->add(new Constraint('whbin_id', '=', $_bin_id));
		}
		
		$balance->loadBy($cc);
		$balances = ($balance->isLoaded()) ? $balance->balance : 0;
		
		if (isset($this->_data['ajax']))
		{
			$this->view->set('value', $balances);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $balances;
		}
		
	}

	public function getTransferDetails ($_whaction_id='') {
// Used by Ajax to get the From/To Locations/Bins based on Stock Item
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['entry_point'])) { $_entry_point=$this->_data['entry_point']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id=$this->_data['whaction_id']; }
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
			if(!empty($this->_data['from_whlocation_id'])) { $_from_location_id=$this->_data['from_whlocation_id']; }
			if(!empty($this->_data['from_whbin_id'])) { $_from_bin_id=$this->_data['from_whbin_id']; }
			if(!empty($this->_data['to_whlocation_id'])) { $_to_location_id=$this->_data['to_whlocation_id']; }
		} else {
// if this is Save and Add Another then need to get $_POST values to set context
			$_stitem_id=isset($_POST[$this->modeltype]['stitem_id'])?$_POST[$this->modeltype]['stitem_id']:'';
			$_from_location_id=isset($_POST[$this->modeltype]['from_whlocation_id'])?$_POST[$this->modeltype]['from_whlocation_id']:'';
		}
//		echo '$_stitem_id='.$_stitem_id.'<br>';
		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

// ****************************************************************************
// Get the To Locations for the selected action
		$from_locations=$this->getFromLocations($_whaction_id);
		$from_whlocation_ids=array_keys($from_locations);
		if (empty($_entry_point) || $_entry_point==$this->modeltype.'_stitem_id') {
			$this->view->set('from_locations',$from_locations);
			if (empty($_from_location_id)) {
				$_from_location_id=key($from_locations);
			}
			$this->view->set('from_whlocation',$from_locations[$_from_location_id]);
			$output['from_whlocation_id']=array('data'=>$from_locations,'is_array'=>is_array($from_locations));
		}
		elseif (empty($_from_location_id))
		{
			$_from_location_id=key($from_locations);
		}
		$this->view->set('from_whlocation_id',$_from_location_id);
//		echo '$_from_location_id='.$_from_location_id.'<br>';
		$from_location=new WHLocation();
		$from_location->load($_from_location_id);
		
// ****************************************************************************
// Get the Stock Item list if no stock item is selected
		$stitem=new STItem();
		if (empty($_entry_point)) {
// No item selected so get list of items and set default as first in list
			$stock_items=array();
			if ($from_location->haveBalances($from_whlocation_ids)) {
				$stock_items=STBalance::getStockList($from_whlocation_ids);
			} else {
				$cc = new ConstraintChain;
				$cc->add(new Constraint('obsolete_date', 'is', 'NULL'));
				$stock_items=$stitem->getAll($cc);
			}
			if (empty($_stitem_id)) {
				$_stitem_id=key($stock_items);
			}
			$this->view->set('stock_item',$stock_items[$_stitem_id]);
			$this->view->set('stock_items',$stock_items);
			$output['stitem_id']=array('data'=>$stock_items,'is_array'=>is_array($stock_items));
		}
		if (empty($_entry_point) || $_entry_point==$this->modeltype.'_stitem_id') {
			$_entry_point=$this->modeltype.'_from_whlocation_id';
		}
//		echo '$_stitem_id='.$_stitem_id.'<br>';
		$stitem->load($_stitem_id);
		$this->view->set('stitem_id',$_stitem_id);
		$this->view->set('uom', $stitem->uom_name);
		$output['uom_id']=array('data'=>$stitem->uom_name,'is_array'=>is_array($stitem->uom_name));
		
// ****************************************************************************
// Get the list of bins for the To Location if it is bin controlled
		if ($_entry_point==$this->modeltype.'_from_whlocation_id')
		{
			$from_bins=array();
			if ($from_location->isBinControlled())
			{
				$from_bins=$stitem->getBinList($_from_location_id);
				$this->view->set('from_bins',$from_bins);
				// check if the input bin present and exists in the bin list
				// if not, check for an error (exists in post data)
				// then check if in bin list; if not, use first in bin list
				if (empty($_from_bin_id) || !isset($from_bins[$_from_bin_id]))
				{
					if (isset($_POST[$this->modeltype]['from_whbin_id']))
					{
						$_from_bin_id=$_POST[$this->modeltype]['from_whbin_id'];
						if (!isset($from_bins[$_from_bin_id]))
						{
							$_from_bin_id=key($from_bins);
						}
					}
					else
					{
						$_from_bin_id=key($from_bins);
					}
				}
			}
			else
			{
				$_from_bin_id='';
			}
			$output['from_whbin_id']=array('data'=>$from_bins,'is_array'=>is_array($from_bins));
		}
//echo 'SttransactionsController::getTransferDetails bins<pre>'.print_r($from_bins,true).'</pre><br>';
		
// ****************************************************************************
// Get the balance of the selected Item for the selected From Location/Bin
		if ($from_location->isBalanceEnabled()) {
			$balance=$this->getBalance($_stitem_id, $_from_location_id, $_from_bin_id);
			$this->view->set('balance',$balance);
			$output['balance']=array('data'=>$balance,'is_array'=>is_array($balance));
		} else {
			$output['balance']='';
		}
		
// ****************************************************************************
// get the associated 'To Location' values for the selected from location
		if ($_entry_point==$this->modeltype.'_from_whlocation_id') {
			$to_locations=$this->getToLocations($_from_location_id, $_whaction_id);
			$this->view->set('to_locations',$to_locations);
			$this->view->set('to_whlocation',$to_locations[$_to_location_id]);
//			if (empty($_to_location_id)) {
				$_to_location_id=key($to_locations);
//			}
			$output['to_whlocation_id']=array('data'=>$to_locations,'is_array'=>is_array($to_locations));
			$_entry_point=$this->modeltype.'_to_whlocation_id';
		}
			
		$this->view->set('to_whlocation_id',$_to_location_id);
		
		$to_location=new WHLocation();
		$to_location->load($_to_location_id);

// ****************************************************************************
// Get the bin list for the To Location if it is bin controlled
		if ($_entry_point==$this->modeltype.'_to_whlocation_id') {
			$to_bins=array();
			if ($to_location->isBinControlled()) {
				$to_bins=$this->getBinList($_to_location_id);
				$this->view->set('to_bins',$to_bins);
			}
			$output['to_whbin_id']=array('data'=>$to_bins,'is_array'=>is_array($to_bins));
		}
			
		if ($ajax) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}

	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Stock transactions':$base), $action);
	}

}
?>
