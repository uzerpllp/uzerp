<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class PinvoicelinesController extends printController {

	protected $version='$Revision: 1.6 $';
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('PInvoiceLine');
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'edit');
		parent::index(new PInvoiceLineCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'tag'=>'new_PInvoiceLine',
					'link'=>array_merge($this->_modules
									   ,array('controller'=>$this->name
											, 'action'=>'new'
											)
									   )
							)
				)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete($modelName = null){
		if (empty($this->_data['PInvoiceLine']['id'])) {
			$this->dataError();
			sendBack();
		}
		$flash = Flash::Instance();
		$pinvoiceline=$this->_uses[$this->modeltype];
		$pinvoiceline->load($this->_data['PInvoiceLine']['id']);
		if ($pinvoiceline->isLoaded() && $pinvoiceline->delete()) {
			$flash->addMessage('Purchase '.$pinvoiceline->invoice_detail->getFormatted('transaction_type').' Line Deleted');
			if (isset($this->_data['dialog'])) {
				$link=array('modules'=>$this->_modules,
							'controller'=>'pinvoices',
							'action'=>'view',
							'other'=>array('id'=>$pinvoiceline->invoice_id)
				);
				$flash->save();
				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			} else {
				sendTo('pinvoices', 'view', $this->_modules, array('id'=>$pinvoiceline->invoice_id));
			}
		}
		$flash->addError('Error deleting '.$pinvoiceline->invoice_detail->getFormatted('transaction_type').' Line');
		$this->_data['id']=$this->_data['PInvoiceLine']['id'];
		$this->_data['invoice_id']=$this->_data['PInvoiceLine']['invoice_id'];
		$this->refresh();
	}
	
	public function _new() {

		$flash=Flash::Instance();

		parent::_new();
		
// Get the Purchase Invoice Line Object - if loaded, this is an edit
		$pinvoiceline = $this->_uses[$this->modeltype];
		
		if (!$pinvoiceline->isLoaded()) {
			if (empty($this->_data['invoice_id'])) {
				$flash->addError('No Purchase Invoice supplied');
				sendBack();
			}
			$pinvoiceline->invoice_id=$this->_data['invoice_id'];
		} else {
		}
		$pinvoice=DataObjectFactory::Factory('Pinvoice');
		$pinvoice->load($pinvoiceline->invoice_id);
		
		if (isset($this->_data[$this->modeltype])) {
		// We've had an error so refresh the page
			$pinvoiceline->line_number=$this->_data['PInvoiceLine']['line_number'];
			$_glaccount_id=$this->_data['PInvoiceLine']['glaccount_id'];
		} elseif ($pinvoiceline->isLoaded()) {
			$_glaccount_id=$pinvoiceline->glaccount_id;
		} else {
			$pinvoiceline->due_delivery_date=$pinvoice->due_date;
		}
		$glaccounts=$this->getAccount();
		$this->view->set('glaccount_options', $glaccounts);
		if (empty($_glaccount_id)) {
			$_glaccount_id=key($glaccounts);
		}
		$this->view->set('glcentre_options', $this->getCentres($_glaccount_id));
		$this->view->set('taxrate_options', $this->getTaxRate());
		$this->view->set('pinvoice', $pinvoice);
		
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
		$flash=Flash::Instance();
		$errors=array();
		
		$data=$this->_data['PInvoiceLine'];
		if (empty($data['invoice_id']))
		{
			$errors[]='Invoice header not defined';
		}
		else
		{
			$pinvoice=DataObjectFactory::Factory('Pinvoice');
			$pinvoice->load($data['invoice_id']);
			if (!$pinvoice->isLoaded())
			{
				$errors[]='Cannot find invoice header';
			}
			elseif ($pinvoice->isLatest($this->_data['PInvoice'], $errors))
			{
				$pinvoiceline=PInvoiceLine::PInvoiceLineFactory($pinvoice, $data, $errors);
				if ($pinvoiceline && count($errors)==0)
				{
					if (!$pinvoiceline->save($pinvoice))
					{
						$errors[]='Failed to save Purchase '.$pinvoice->getFormatted('transaction_type').' line';
					}
				}
			}
		}
		if(count($errors)==0) {
			$flash->addMessage('Purchase '.$pinvoice->getFormatted('transaction_type').' Line Saved');
			if (isset($this->_data['saveAnother'])) {
				$other=array('invoice_id'=>$pinvoiceline->invoice_id);
				if (isset($this->_data['dialog'])) {
					$other+=array('dialog'=>'');
				}
				if (isset($this->_data['ajax'])) {
					$other+=array('ajax'=>'');
				}
				sendTo($this->name, 'new', $this->_modules, $other);
			} else {
				$action='view';
				$controller='pinvoices';
				$other=array('id'=>$pinvoiceline->invoice_id);
			}
			if (isset($this->_data['dialog'])) {
				$link=array('modules'=>$this->_modules,
							'controller'=>$controller,
							'action'=>$action,
							'other'=>$other
				);
				$flash->save();
				echo parent::returnJSONResponse(TRUE,array('redirect'=>'/?'.setParamsString($link)));
				exit;
			} else {
				sendTo($controller, $action, $this->_modules, $other);
			}
		} else {
			$flash->addErrors($errors);
			$this->_data['id']=$this->_data['PInvoiceLine']['id'];
			$this->_data['invoice_id']=$this->_data['PInvoiceLine']['invoice_id'];
			$this->refresh();
		}

	}

// Ajax stuff!
	public function getAccount() {
// Used by Ajax to return Account list after selecting the Product

		$account_list = array();
		$account = DataObjectFactory::Factory('GLAccount');
		$cc = new ConstraintChain();
		$cc->add(new Constraint('control', '=', 'FALSE'));
		$account_list = $account->getAll($cc);
		
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$account_list);
			$this->setTemplateName('select_options');
		} else {
			return $account_list;
		}
	}

	public function getCentres($_id='') {
// Used by Ajax to return Centre list after selecting the Account

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['id'])) { $_id=$this->_data['id']; }
		}
		
		$account = DataObjectFactory::Factory('GLAccount');
		$account->load($_id);
		$centres = $account->getCentres();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$centres);
			$this->setTemplateName('select_options');
		} else {
			return $centres;
		}
	}

	public function getTaxRate() {
// Used by Ajax to return Tax Rate list after selecting the Product Line

		$tax_rate_list = array();
		$tax_rate = DataObjectFactory::Factory('TaxRate');
		$tax_rate_list=$tax_rate->getAll();
	
		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$tax_rate_list);
			$this->setTemplateName('select_options');
		} else {
			return $tax_rate_list;
		}
	}
	
	protected function getPageName($base=null, $action=null) {
		return parent::getPageName((!empty($base))?$base:'purchase_invoice_line',$action);
	}
	
}

// End of PinvoicelinesController
