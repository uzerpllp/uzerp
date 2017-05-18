<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhlocationsController extends ManufacturingController {

	protected $version='$Revision: 1.12 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHLocation();

		$this->uses($this->_templateobject);

	}

	public function index(){
		$errors=array();
		$s_data=array();
// Set context from calling module
		if (isset($this->_data['whstore_id'])) {
			$s_data['whstore_id'] = $this->_data['whstore_id'];
		}
		
		$this->setSearch('whlocationsSearch', 'useDefault', $s_data);

		$id = $this->search->getValue('whstore_id');
		$this->view->set('clickaction', 'view');
		parent::index(new WHLocationCollection($this->_templateobject));

		$store=new WHStore();
		$store->load($id);
		$this->view->set('whstore',$store);
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['stores'] = array('tag' => 'View All Stores'
									  ,'link' => array_merge($this->_modules
															,array('controller'=>'WHStores'
																  ,'action'=>'index'
																  )
															)
									  );
		$sidebarlist['new'] = array('tag'=>'New Location for '.$store->getIdentifierValue()
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>$this->name
												 			 ,'action'=>'new'
															 ,'whstore_id'=>$id
															 )
														)
									);
		$sidebarlist['edit'] = array('tag'=>'Edit '.$store->getIdentifierValue()
									,'link'=>array_merge($this->_modules
														,array('controller'=>'WHStores'
												  			  ,'action'=>'edit'
												  			  ,'id'=>$id
												  			  )
												  		 )
									);
		if ($store->locations->count()===0) {
			$sidebarlist['delete'] = array('tag'=>'Delete '.$store->getIdentifierValue()
										  ,'link'=>array_merge($this->_modules
															  ,array('controller'=>'WHStores'
																	,'action'=>'delete'
																	,'id'=>$id
																	)
															  )
										  );
		}
		$sidebar->addList('Actions',$sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function _new() {
		
		parent::_new();
		
		$whlocation=$this->_uses[$this->modeltype];
		
		$account=new GLAccount();
		$glaccounts=array(''=>'None');
		$glaccounts+=$account->nonControlAccounts();
		$this->view->set('accounts',$glaccounts);
		
		if (isset($_POST[$this->modeltype]['glaccount_id'])) {
			$default_glaccount_id=$_POST[$this->modeltype]['glaccount_id'];
		} elseif ($whlocation->isLoaded()) {
			$default_glaccount_id=$whlocation->glaccount_id;
		} else {
			$default_glaccount_id='';
		}
		$this->view->set('centres',$this->getCentres($default_glaccount_id));

	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$transaction=$this->_uses[$this->modeltype];
		$id=$transaction->id;
				
		if ($transaction->bin_controlled=='t') {
			sendTo('WHBins','index',$this->_modules,array('whlocation_id'=>$this->_data['id']));
		}
				
		$this->view->set('transaction',$transaction);

		$sidebar=new SidebarController($this->view);
		$sidebarlist=array();
		
		$sidebarlist['stores']= array(
					'tag' => 'All Stores',
					'link' => array_merge($this->_modules
										 ,array('controller'=>'WHStores'
											   ,'action'=>'index'
											   )
										 )
							);
		$sidebarlist['locations']= array(
					'tag' => 'Locations for Store '.$transaction->whstore,
					'link' => array_merge($this->_modules
										 ,array('controller'=>'WHStores'
											   ,'action'=>'view'
											   ,'id'=>$transaction->whstore_id
											   )
										 )
							);
		
		$sidebar->addList('Show',$sidebarlist);

		$sidebarlist=array();
		$sidebarlist['edit']= array(
					'tag' => 'Edit',
					'link' => array_merge($this->_modules
										 ,array('controller'=>$this->name
											   ,'action'=>'edit'
											   ,'id'=>$id
											   )
										 )
							);
		if (($transaction->isBinControlled() && $transaction->bins->count()==0)
			|| (!$transaction->isBinControlled() && $transaction->balances->count()==0)) {
			$sidebarlist['delete']= array(
					'tag' => 'Delete',
					'link' => array_merge($this->_modules
										 ,array('controller'=>$this->name
											   ,'action'=>'delete'
											   ,'id'=>$id
											   ,'whstore_id'=>$transaction->whstore_id
											   )
										 )
							);
		}
		
		$sidebar->addList('This Location',$sidebarlist);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('Location'));

	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'locations':$base), $action);
	}
	
	public function getCentres($_id='') {
// Used by Ajax to return Centre list after selecting the Account
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account = new GLAccount;
		$account->load($_id);
		$centres = $account->getCentres();
		
		if (empty($centres)) {
			$centres=array(''=>'None');
		}
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		} else {
			return $centres;
		}

	}

}

?>