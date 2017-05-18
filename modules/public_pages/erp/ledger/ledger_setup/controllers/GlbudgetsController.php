<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GlbudgetsController extends LedgerController {

	protected $version='$Revision: 1.7 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new GLBudget();
		$this->uses($this->_templateobject);

	}

	public function index(){

		$errors=array();
		$s_data=array();

// Preserve any search criteria selection so that the context is maintained
		if(isset($this->_data['Search'])) {
			$s_data=$this->_data['Search'];
		} elseif (!isset($this->_data['orderby'])
				&& !isset($this->_data['page'])) {
// Either this is the first entry to the page or the search has been cleared
// and orderby or paging is not selected
// so set context from calling module
			$currentPeriod=GLPeriod::getPeriod(date('Y-m-d'));
			if (($currentPeriod) && (count($currentPeriod) > 0)) {
				$s_data['glperiods_id']=array($currentPeriod['id']);
			}
			if (isset($this->_data['glaccount_id'])) {
				$s_data['glaccount_id']=$this->_data['glaccount_id'];
			} 
			if (isset($this->_data['glcentre_id'])) {
				$s_data['glcentre_id']=$this->_data['glcentre_id'];
			}
		}

		$this->setSearch('glbalancesSearch', 'useDefault', $s_data);
		
		$this->view->set('clickaction', 'edit');
		parent::index(new GLBudgetCollection($this->_templateobject));		
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['new']= array(
							'tag'=>'New Budget',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
													 ,'action'=>'new'
													 )
											   )
				);
		$sidebarlist['viewaccounts']= array(
							'tag'=>'View All Accounts',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'glaccounts'
													 ,'action'=>'index'
													 )
											   )
				);
		$sidebarlist['viewcentres']= array(
							'tag'=>'View All Centres',
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'glcentres'
													 ,'action'=>'index'
													 )
											   )
				);
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		parent::_new();
		
		$glaccount=new GLAccount();
		$accounts=$glaccount->nonControlAccounts();
		$this->view->set('accounts',$accounts);
		
		$budget=$this->_uses[$this->modeltype];
		if (isset($_POST[$this->modeltype]['glaccount_id'])) {
			$default_account_id=$_POST[$this->modeltype]['glaccount_id'];
		} elseif ($budget->isLoaded()) {
			$default_account_id=$budget->glaccount_id;
		} else {
			$account=new GLAccount();
			$accounts=$account->getAll();
			$default_account_id=key($accounts);
		}
		$this->view->set('centres',$this->getCentres($default_account_id));
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'general_ledger_budgets':$base), $action);
	}

}
?>
