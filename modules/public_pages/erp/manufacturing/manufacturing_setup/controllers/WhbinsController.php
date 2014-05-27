<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WhbinsController extends ManufacturingController {

	protected $version='$Revision: 1.9 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHBin();
		$this->uses($this->_templateobject);
	}

	public function index(){
		$errors=array();
		$s_data=array();
		$whlocation=new WHLocation();
// Set context from calling module
		if (isset($this->_data['whlocation_id'])) {
			$s_data['whlocation_id'] = $this->_data['whlocation_id'];
		}
		
		$this->setSearch('whbinsSearch', 'useDefault', $s_data);

		$whlocation_id=$this->search->getValue('whlocation_id');
		if (!empty($whlocation_id)) {
			$whlocation->load($whlocation_id);
		}
		$this->view->set('whlocation', $whlocation);
		$this->view->set('clickaction', 'view');
		parent::index(new WHBinCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['all_stores'] = array('tag' => 'all stores'
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>'WHLocations'
															 ,'action'=>'index'
															 ,'whstore_id'=>$whlocation->whstore_id
															 )
													   ),
									);
		$sidebarlist['all_locations'] = array('tag' => 'locations for store '.$whlocation->whstore
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>'WHLocations'
															 ,'action'=>'index'
															 ,'whstore_id'=>$whlocation->whstore_id
															 )
													   ),
									);
		$sidebar->addList('Show',$sidebarlist);
		
		$sidebarlist=array();
		$sidebarlist['edit'] = array('tag' => 'Edit'
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>'WHLocations'
															 ,'action'=>'edit'
															 ,'id'=>$whlocation->id
															 )
													   ),
									);
		$sidebarlist['new'] = array('tag' => 'New Bin'
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>$this->name
															 ,'action'=>'new'
															 ,'whlocation_id'=>$whlocation->id
															 )
													   ),
									);
		if (!$whlocation->isBinControlled() || $whlocation->bins->count()==0) {
			$sidebarlist['delete'] = array('tag' => 'Delete'
								   ,'link'=>array_merge($this->_modules
													   ,array('controller'=>'WHLocations'
															 ,'action'=>'delete'
															 ,'id'=>$whlocation->id
															 )
													   ),
									);
		}
		
		$sidebar->addList('This Location',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		$this->view->set('page_title', $this->getPageName('Location','View Bins for'));
		
	}

	public function _new(){
		parent::_new();
		
		$whbin=$this->_uses[$this->modeltype];
		
		if ($whbin->isLoaded()) {
			$whlocation_id=$whbin->whlocation_id;
		} elseif (isset($this->_data['whlocation_id'])) {
			$whlocation_id=$this->_data['whlocation_id'];
		} else {
			$whlocation_id='';
		}
		
		if (!empty($whlocation_id)) {
			$transaction= new WHLocation();
			$transaction->load($whlocation_id);
			$this->view->set('whlocation', $transaction->getIdentifierValue());
			$whstore=$transaction->whstore;
		} else {
			$whstore='';
		}
		$this->view->set('whstore', $whstore);
	}
	
	public function view() {
		if (!$this->loadData()) {
			$this->dataError();
			sendBack();
		}
		$whbin=$this->_uses[$this->modeltype];
		$id=$whbin->id;
		$this->view->set('transaction',$whbin);
		$whlocation=new WHLocation();
		$whlocation->load($whbin->whlocation_id);
		$this->view->set('whstore', $whlocation->whstore);
		$sidebar=new SidebarController($this->view);

		$sidebarlist=array();
		$sidebarlist['stores'] = array('tag' => 'All Stores'
									  ,'link' => array_merge($this->_modules
															,array('controller'=>'WHStores'
																  ,'action'=>'index'
																  )
															)
									  );
		$sidebarlist['locations'] = array('tag' => 'Locations for Store '.$whlocation->whstore
										 ,'link' => array_merge($this->_modules
															   ,array('controller'=>'WHLocations'
																	 ,'action'=>'index'
																	 ,'id'=>$whbin->whlocation_id
																	 )
															   )
								   );
		$sidebarlist['bins'] = array('tag' => 'Bins for Location '.$whlocation->getIdentifierValue()
									,'link' => array_merge($this->_modules
														  ,array('controller'=>'WHLocations'
																,'action'=>'view'
																,'id'=>$whbin->whlocation_id
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
											   ,'whlocation_id'=>$whbin->whlocation_id
											   )
										 )
					);
// Do not allow delete if balances exist
		if ($whbin->balances->count()===0) {
			$sidebarlist['delete']= array(
						'tag' => 'Delete',
						'link' => array_merge($this->_modules
											 ,array('controller'=>$this->name
												   ,'action'=>'delete'
												   ,'id'=>$id
												   ,'whlocation_id'=>$whbin->whlocation_id
												   )
											 )
						);
		}
		$sidebar->addList('This Bin',$sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'bins':$base), $action);
	}

}
?>