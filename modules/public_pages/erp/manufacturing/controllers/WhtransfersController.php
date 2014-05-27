<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class WHtransfersController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new WHTransfer();
		$this->uses($this->_templateobject);
	
	}

	public function index(){
		$this->view->set('clickcontroller', 'whtransferlines');
		$this->view->set('clickaction', 'index');
		parent::index(new WHTransferCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array('tag'=>'New Transfer'
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

	public function cancel() {
		$flash = Flash::Instance();
		$errors=array();
		
		$whtransfer = new WHTransfer();
		if (isset($this->_data['id'])) {
			$whtransfer->load($this->_data['id']);
		} else {
			$errors[]='Select a Transfer Number';
		}
		
		if ($whtransfer) {
			if ($whtransfer->cancel() && count($errors)==0) {
				$flash->addMessage('Transfer has been canceled');
			} else {
				$errors[]='Failed to cancel transfer';
			}
		} else {
			$errors[]='Failed to find Transfer details';
		}
		if (count($errors)>0) {
			$flash->addErrors($errors);
		}
		sendTo($this->name
                     ,'index'
                     ,$this->_modules);
	}
	
	public function edit() {
		parent::edit();
		$whtransfer = new WHTransfer();
		$whtransfer->load($this->_data['id']);
		$this->view->set('whtransfer',$whtransfer);
		
		$stitems=STBalance::getStockList($whtransfer->from_whlocation_id);
		$this->view->set('stitems',$stitems);
		$this->_templateName = $this->getTemplateName('_new');
	}
	
	public function _new(){
		$flash = Flash::Instance();
// Get the Action		
		$whaction=new WHAction();
		$whactions=$whaction->getActions('T');
		$this->view->set('transfer_actions',$whactions);
// Display the New/Edit screen
  		parent::_new();
	}
	
	public function save() {
		$flash=Flash::Instance();
		
		if (strtolower($this->_data['saveform'])=='cancel') {
			$flash->addMessage('Action cancelled');
			sendTo($this->name
                      ,'index'
                      ,$this->_modules);
			
		}
		$errors=array();
		$db=DB::Instance();
		$db->StartTrans();
		
		$header_data = $this->_data['WHTransfer'];
		$lines_data = array();
		if (isset($this->_data['WHTransferLine'])) {
			$lines_data=$this->_data['WHTransferLine'];
			$lines_data = DataObjectCollection::joinArray($lines_data);
		} else {
			$errors[]='No Transfer Lines entered';
		}

		if (isset($header_data['id']) && $header_data['id']!='') {
			$action='updated';
		// delete any lines not submitted
			$update=array();
			foreach ($lines_data as $line) {
				$update[$line['id']]=$line['id'];
			}
			$whtransfer=new WHTransfer();
			$whtransfer->load($header_data['id']);
			if ($whtransfer) {
				foreach ($whtransfer->transfer_lines as $line) {
					if (!isset($update[$line->id])) {
						$whtransferline=new WHTransferline();
						$whtransferline->delete($line->id);
					}
				}
			}
		} else {
			$action='added';
		}
		
		$whtransfer=WHTransfer::Factory($header_data, $lines_data, $errors);
		
		if ($whtransfer && count($errors)==0) {
			$whtransfer->save($errors);
		}
		
		if(count($errors)==0 && $db->CompleteTrans()) {
			$flash->addMessage('Transfer Number '.$whtransfer->transfer_number.' '.$action.' successfully');
			sendTo($this->name
                      ,'index'
                      ,$this->_modules);
		} else {
			$db->FailTrans();
			$flash->addErrors($errors);
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}

	protected function getPageName($base=null,$action=null) {
		return parent::getPageName((empty($base)?'Warehouse Transfers':$base),$action);
	}

}
?>
