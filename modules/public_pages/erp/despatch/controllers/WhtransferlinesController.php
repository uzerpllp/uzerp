<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHtransferlinesController extends Controller {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHTransferline();
		$this->uses($this->_templateobject);
	
	}

	public function view(){
		$flash=Flash::Instance();

// Preserve any search criteria selection so that the context is maintained
		$s_data=array();
		if(isset($this->_data['id'])) {
			$s_data['wh_transfer_id']=$this->_data['id'];
		}
		
		$whtransfer=new WHTransfer();
		$whtransfer->load($this->_data['id']);
		$this->view->set('whtransfer',$whtransfer);
  		$from_store=WHLocation::getStoreLocation($whtransfer->from_whlocation_id);
  		$this->view->set('from_store',$from_store);
  		$to_store=WHLocation::getStoreLocation($whtransfer->to_whlocation_id);
  		$this->view->set('to_store',$to_store);
		
  		$this->setSearch('whtransfersSearch', 'useDefault', $s_data);
  		
  		parent::index(new WHTransferlineCollection($this->_templateobject));
  		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['awaiting']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'WHTransfers'
											  		 ,'action'=>'selectWHTransfers'
											  		 )
											   ),
							'tag'=>'View Transfers Awaiting Despatch'
							);
		$sidebarlist['completed']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'WHTransfers'
											  		 ,'action'=>'viewWHTransfers'
											  		 )
											   ),
							'tag'=>'View Completed Transfers'
							);
								  
		if ($whtransfer->awaitingTransfer()) {
			$sidebarlist['edit']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'WHTransfers'
													 ,'action'=>'transferStock'
													 ,'id'=>$whtransfer->id
													 )
											   ),
							'tag'=>'Action this Transfer'
							);
		}
		
		if ($whtransfer->transferred()) {
			$sidebarlist['edit']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>'WHTransfers'
													 ,'action'=>'printaction'
													 ,'printaction'=>'printTransferNote'
													 ,'filename'=>'WHT'.$whtransfer->transfer_number
													 ,'id'=>$whtransfer->id
													 )
											   ),
							'tag'=>'Reprint Transfer Note'
							);
		}
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Warehouse Transfers':$base),$action);
	}

}
?>
