<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHtransferlinesController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHTransferline();
		$this->uses($this->_templateobject);
	
	}

	public function index(){
		$flash=Flash::Instance();

// Preserve any search criteria selection so that the context is maintained
		$s_data=array();
		if(isset($this->_data['id'])) {
			$s_data['wh_transfer_id']=$this->_data['id'];
		}
		
		$this->view->set('clickaction', 'view');
		
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
		$sidebarlist['all']=array('tag'=>'View All Transfers'
								 ,'link'=>array_merge($this->_modules
													 ,array('controller'=>'WHTransfers'
														   ,'action'=>'index'
														   )
													 )
								 );

		if ($whtransfer->awaitingTransfer()) {
			$sidebarlist['edit']=array('tag'=>'Edit this Transfer'
									  ,'link'=>array_merge($this->_modules
														  ,array('controller'=>'WHTransfers'
																,'action'=>'edit'
																,'id'=>$whtransfer->id
																)
														  )
									  );
			$sidebarlist['cancel']=array('tag'=>'Cancel this Transfer'
										,'link'=>array_merge($this->_modules
															,array('controller'=>'WHTransfers'
																  ,'action'=>'cancel'
																  ,'id'=>$whtransfer->id
																  )
															 )
										);

		}
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function view() {
		if (!isset($this->_data['id'])) {
			$flash->addError('Select an item');
		} else {
			$whtransferline=new WHTransferline();
			$whtransferline->load($this->_data['id']);
			if ($whtransferline) {
				sendTo('stitems', $this->_data['action'], $this->_modules, array('id'=>$whtransferline->stitem_id));
			} else {
				$flash->addError('Error loading item');
			}
		}
		
	}
	
	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Warehouse Transfers':$base),$action);
	}

}
?>
