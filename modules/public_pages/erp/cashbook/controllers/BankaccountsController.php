<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class BankaccountsController extends Controller {

	protected $version='$Revision: 1.20 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new CBAccount();
		$this->uses($this->_templateobject);

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		parent::index(new CBAccountCollection($this->_templateobject));
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'ledger_setup'
								 ,'controller'=>$this->name
								 ,'action'=>'new'
									   ),
					'tag'=>'new_bank_account'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$account = $this->_uses[$this->modeltype];
		
		$glbalance = $account->glbalance();
		$this->view->set('glbalance', $glbalance);
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['view_all'] = array(
					'tag' => 'View All Accounts',
					'link'=>array('modules' => $this->_modules
								 ,'controller'=>$this->name
								 ,'action'=>'index'
								 )
				);
		
		$sidebarlist['receiveany'] = array(
					'link'=>array('modules' => $this->_modules
								 ,'controller'=>'cbtransactions'
								 ,'action'=>'receive_payment'
								 ,'cb_account_id'=>$account->id
								 ),
					'tag'=>'receive_payment'
				);
		
		$sidebarlist['makeany'] = array(
					'link'=>array('modules' => $this->_modules
								 ,'controller'=>'cbtransactions'
								 ,'action'=>'make_payment'
								 ,'cb_account_id'=>$account->id
								 ),
					'tag'=>'make_payment'
				);
		
		$sidebarlist['moveany'] = array(
					'link'=>array('modules' => $this->_modules
								 ,'controller'=>'cbtransactions'
								 ,'action'=>'move_money'
								 ),
					'tag'=>'move_money'
				);
		
		$sidebar->addList(
			'actions',
			$sidebarlist
		);
		
		$sidebarlist = array();
		
		$idfield		= $account->idField;
		$idfieldValue	= $account->{$account->idField};
		
		$sidebarlist[$account->name] = array(
					'tag' => $account->name,
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'view'
								 ,$idfield		=> $idfieldValue
								 )
				);
		
		$sidebarlist['reconcile'] = array(
					'tag'=>'reconcile',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=>'reconcile'
								 ,$idfield		=> $idfieldValue
								 )
				);
		
		$sidebarlist['viewtrans'] = array(
					'link'=>array('modules'			=> $this->_modules
								 ,'controller'		=> 'cbtransactions'
								 ,'action'			=> 'index'
								 ,'cb_account_id'	=> $idfieldValue
								 ),
					'tag'=>'view_transactions'
				);
		
		$sidebarlist['receive_payment'] = array(
					'link'=>array('modules'			=> $this->_modules
								 ,'controller'		=> 'cbtransactions'
								 ,'action'			=> 'receive_payment'
								 ,'cb_account_id'	=> $idfieldValue
								 ,'currency_id'		=> $account->currency_id
								 ),
					'tag'=>'receive_payment'
				);
		
		$sidebarlist['make_payment'] = array(
					'link'=>array('modules'			=> $this->_modules
								 ,'controller'		=> 'cbtransactions'
								 ,'action'			=> 'make_payment'
								 ,'cb_account_id'	=> $idfieldValue
								 ,'currency_id'		=> $account->currency_id
								 ),
					'tag'=>'make_payment'
				);
		
		$sidebarlist['move_money'] = array(
					'link'=>array('modules'			=> $this->_modules
								 ,'controller'		=> 'cbtransactions'
								 ,'action'			=> 'move_money'
								 ,'cb_account_id'	=> $idfieldValue
								 ),
					'tag'=>'move_money'
				);
		
		$params = new GLParams();
		if ($params->base_currency() != $account->currency_id)
		{
			$sidebarlist['revaluation'] = array(
					'link'=>array('modules'			=> $this->_modules
								 ,'controller'		=> $this->name
								 ,'action'			=> 'revaluation'
								 ,$idfield			=> $idfieldValue
								 ),
					'tag'=>'revaluation'
				);
			
		}
		
		$sidebar->addList(
			$account->name,
			$sidebarlist
		);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
	}
	
	public function reconcile() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$account = $this->_uses[$this->modeltype];
		
		$s_data	= array();
		
		$transactions = new CBTransactionCollection(new CBTransaction);
		$sh = new SearchHandler($transactions, false);
//		$sh->extract();
		$sh->setLimit(375);
		$sh->addConstraint(new Constraint('cb_account_id','=',$this->_data['id']));
		$sh->addConstraint(new Constraint('status','=','N'));
		$sh->setOrderby(array('transaction_date', 'payment_type', 'reference', 'ext_reference'));
		parent::index($transactions, $sh);
		$this->view->set('num_records',$transactions->num_records);
		$this->view->set('num_pages',1);
		$this->view->set('cur_page',1);
		
		$this->view->set('transactions',$transactions);
		
		$this->view->set('CBAccount',$account);
		$this->view->set('clickcontroller', 'cbtransactions');
		$this->view->set('clickaction', 'view');
		
	}
	
	public function save_reconciliation() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$account		= $this->_uses[$this->modeltype];
		$data			= $this->_data[$this->modeltype];
		$start_balance	= $account->statement_balance;
		$new_balance	= $data['statement_balance'];
		$new_page		= $data['statement_page'];
		$new_date		= fix_date($data['statement_date']);
		$total			= 0;
		
		$flash = Flash::Instance();
		
		if (!isset($this->_data['transactions'])) {
			$flash->addError('You must select at least one transaction');
		} else {
			
			$transactions = $this->_data['transactions'];
			
			foreach ($transactions as $id => $on) {
				$trans = new CBTransaction();
				$trans->load($id);
				$total = bcadd($total, $trans->gross_value);
				$trans_store[] = $trans;
			}
			
			$db = DB::Instance();
			$db->StartTrans();
			
			if (bcadd($start_balance, $total) == $new_balance) {
				
				foreach($trans_store as $transaction) {
					
					$result = $transaction->update(
						$transaction->id,
						array('statement_date', 'statement_page', 'status'),
						array($new_date, $new_page, 'R')
					);
					
					if ($result === false) {
						$flash->addError('Failed to update Transaction');
						$db->FailTrans();
					}
									
				}
				
				$result = $account->update(
					$data['id'],
					array('statement_balance', 'statement_date', 'statement_page'),
					array($new_balance, $new_date, $new_page)
				);
				
				if ($result !== false) {
					
					$db->CompleteTrans();
					$flash->addMessage('Transactions matched to statement');
					sendTo($this->name, 'view', $this->_modules, array('id'=>$data['id']));
					
				} else {
					
					$flash->addError('Failed to update Account');
					$db->FailTrans();
					$db->CompleteTrans();
					
				}
				
			} else {
				$flash->addError('Selected transactions do not match to new statement balance');
			}
		}
		
		$this->_data['id'] = $data['id'];
		$this->refresh();
		
	}

	public function revaluation() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$account = $this->_uses[$this->modeltype];
		$this->view->set('CBAccount', $account);
		
		$glbalance = $account->glbalance();
		$this->view->set('glbalance', $glbalance);
		
		$rate = $account->currency_detail->rate;
		
		$this->view->set('rate', $rate);
		
		$method = $account->currency_detail->method;
		
		if ($method=='D')
		{
			$new_balance = round($account->balance / $rate ,2);
		}
		else
		{
			$new_balance = round($account->balance * $rate ,2);
		}
		
		$new_balance = bcadd($new_balance ,0);
		
		$this->view->set('method', $method);
		$this->view->set('new_balance', $new_balance);
		$this->view->set('transaction_date', date(DATE_FORMAT));
		
	}
	
	public function save_revaluation() {
		
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		
		$errors = array();
		$flash = Flash::Instance();
		
		$account		= $this->_uses[$this->modeltype];
		$data			= $this->_data[$this->modeltype];
		
		if ($account->glbalance() != $data['new_balance'])
		{
			unset($data[$account->idField]);
			
			if ($account->revalue($data, $errors))
			{
				$flash->addMessage('Account GL Balance updated');
			}
			else
			{
				$flash->addErrors($errors);
				$this->refresh();
			}
		}
		else
		{
			$flash->addMessage('No change to balance');
		}
		
		sendTo($this->name, 'view', $this->_modules, array($account->idField=>$account->{$account->idField}));
		
	}
	
	public function delete($modelName = null) {
		return false;
	}
	
	public function edit() {
		return false;
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		return false;
		
	}

	public function getCurrencyId($_id='') {
// Used by Ajax to return Currency after selecting the Bank Account
		
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account = new CBAccount();
		$account->load($_id);
		$currency='';
		if ($account) {
			$currency=$account->currency_id;
		}

		if(isset($this->_data['ajax'])) {
			$this->view->set('value',$currency);
			$this->setTemplateName('text_inner');
		} else {
			return $currency;
		}
		
	}

	protected function getPageName($base=null,$type=null) {
		return parent::getPageName((empty($base)?'bank_accounts':$base), $type);
	}

}

// End of BankaccountsController
