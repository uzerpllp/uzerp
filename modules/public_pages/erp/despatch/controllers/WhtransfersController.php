<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHtransfersController extends printController {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHTransfer();
		$this->uses($this->_templateobject);
	
	}

	public function selectWHTransfers(){
		$this->view->set('clickcontroller', 'whtransferlines');
		$this->view->set('clickaction', 'view');

		$s_data=array();
		$s_data['status']=WHTransfer::awaitingTransferStatus();
		
		$this->setSearch('whtransfersSearch', 'useDefault', $s_data);
		parent::index(new WHTransferCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['transferred']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
											  		 ,'action'=>'viewWHTransfers'
											  		 )
											   ),
							'tag'=>'View Completed Transfers'
							);
		$sidebar->addList('Actions', $sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('page_title',$this->getPageName('', 'Select'));
	}

	public function viewWHTransfers(){
		$this->view->set('clickcontroller', 'whtransferlines');
		$this->view->set('clickaction', 'view');

		$s_data=array();
		$s_data['status']=WHTransfer::transferredStatus();
		
		$this->setSearch('whtransfersSearch', 'useDefault', $s_data);
		parent::index(new WHTransferCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebarlist=array();
		$sidebarlist['awaiting']=array(
							'link'=>array_merge($this->_modules
											   ,array('controller'=>$this->name
											  		 ,'action'=>'selectWHTransfers'
											  		 )
											   ),
							'tag'=>'View Transfers Awaiting Despatch'
							);
		$sidebar->addList('Actions', $sidebarlist);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('page_title',$this->getPageName('', 'View'));
		$this->_templateName = $this->getTemplateName('selectwhtransfers');
		
	}

	public function transferStock() {
		$flash = Flash::Instance();
		$errors=array();
		$db=DB::Instance();
		$db->StartTrans();
		
		$whtransfer = new WHTransfer();
		if (isset($this->_data['id'])) {
			$whtransfer->load($this->_data['id']);
		} else {
			$errors[]='Select a Transfer Number';
		}
		
		if ($whtransfer) {
			if ($whtransfer->transfer() && count($errors)==0) {
				foreach ($whtransfer->transfer_lines as $lines) {
					$lines->moveStock($whtransfer, $errors);
					if (count($errors)>0) {
						$errors[]='Failed to move stock';
						break;
					}
				}
				
				if ($whtransfer->update($whtransfer->id, 'actual_transfer_date', fix_date(date(DATE_FORMAT)))) {
					$flash->addMessage('Transfer has been completed');
				} else {
					$errors[]='Failed to transfer stock';
				}
			} else {
				$errors[]='Failed to transfer stock';
			}
		} else {
			$errors[]='Failed to find Transfer details';
		}
		if (count($errors)>0) {
			$flash->addErrors($errors);
			$db->FailTrans();
			sendTo('WHTransferlines'
				  ,'view'
				  ,$this->_modules
				  ,array('id'=>$whtransfer->id));
		} else {
			$db->CompleteTrans();
			sendTo($this->name
				  ,'printaction'
				  ,$this->_modules
				  ,array('printaction'=>'printTransferNote'
						,'filename'=>'WHT'.$whtransfer->transfer_number
						,'id'=>$whtransfer->id));
		}
		
		sendTo($this->name
                     ,'selectwhtransfers'
                     ,$this->_modules);

	}
	
	public function printTransferNote() {

		$flash = Flash::Instance();

		if (isset($this->_data['cancel'])) {
			$flash->addMessage('Print Despatch Note Canceled');
			sendBack();
		}
		
		$whtransfer=new WHTransfer();
		$whtransfer->load($this->_data['id']);
		
		$report=new WarehouseTransfer($whtransfer);
		
		$errors=array();
				
		if ($report->setPrintParams($this->_data, $errors)) {
			if ($report->constructPrint()) {
				$flash->addMessage('Print Warehouse Transfer Note Completed');
			} else {
				$flash->addError('Print Warehouse Transfer Note Failed');
			}
		} else {
			$flash->addErrors($errors);
		}

		sendTo($this->name
                     ,'selectwhtransfers'
                     ,$this->_modules);
	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Warehouse Transfers':$base),$action);
	}

}
?>
