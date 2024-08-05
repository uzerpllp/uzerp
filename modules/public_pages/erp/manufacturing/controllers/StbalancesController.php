<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class StbalancesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new STBalance();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		parent::index(new STBalanceCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Stock Balance'
							,'link'=>array_merge($this->_modules
												,array('controller'=>$this->name
													  ,'action'=>'new'
													  )
												)
							)
				 )
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('STBalance');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : bool {
		$flash=Flash::Instance();
		$db = DB::Instance();
		$db->StartTrans();
		if(parent::save('STBalance')&&$db->CompleteTrans()) {
			return true;
		} else {
			$db->FailTrans();
			$flash->addMessage('Balances save failed');
			return false;
		}
	
	}

	Public function getBalance() {
// Function called by Ajax Request to return balance for selected item, location, bin
		$balance=new STBalance();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $this->_data['stitem_id']));
		$cc->add(new Constraint('whlocation_id', '=', $this->_data['location_id']));
		if (isset($this->_data['bin_id'])
			&& !empty($this->_data['bin_id'])) {
			$cc->add(new Constraint('whbin_id', '=', $this->_data['bin_id']));
		}
		$balance->loadBy($cc);
		echo json_encode(($balance->balance)?$balance->balance:0);
		exit;
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName('stock_balances');
	}

}
?>
