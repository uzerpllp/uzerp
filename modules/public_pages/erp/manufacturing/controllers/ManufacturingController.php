<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class ManufacturingController extends printController {

	protected $version='$Revision: 1.8 $';

	public function delete($modelName = null){

		if (!$this->CheckParams($this->_templateobject->idField)) {
			sendBack();
		}

		parent::delete($this->modeltype);

		sendTo($this->name,'index',$this->_modules
			  ,$this->getOtherParams());

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void {

		if (!$this->CheckParams($this->modeltype)) {
			sendBack();
		}

		$flash=Flash::Instance();
		$errors=array();

		if(parent::save($this->modeltype, '', $errors)) {
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		$flash->addErrors($errors);
		$flash->addError('Failed to save '.$this->modeltype);
		$this->refresh();

	}

	public function save_transactions() {
		if (!$this->CheckParams('STTransaction')) {
			sendBack();
		}
		$flash=Flash::Instance();

		$db = DB::Instance();
		$db->StartTrans();
		$errors=array();
		$data=$this->_data['STTransaction'];
		$stitem=new STItem();
		$stitem->load($data['stitem_id']);
		$converted=round((float) $data['qty'],$stitem->qty_decimals);
		if ($converted<>$data['qty']) {
			$errors[]='Quantity can only have '.$stitem->qty_decimals.' decimal places';
		} elseif ($data['qty']<=0) {
			$errors[]='Quantity must be greater than zero';
		} else {
			$models=STTransaction::prepareMove($data, $errors);
			if (count($errors)==0) {
				foreach ($models as $model) {
					$result=$model->save($errors);
					if($result===false) {
						$db->FailTrans();
					}
				}
			}
		}

		if (count($errors)>0) {
			$errors[]='Error transferring stock';
			$db->FailTrans();
			$db->CompleteTrans();
			$flash->addErrors($errors);
			$this->_data['whaction_id']=$data['whaction_id'];
			// Set smarty variables to populate autocomplete search, See StitemsController::searchItems
			$this->view->set('search_text', $stitem->item_code);
			$this->view->set('item_description', substr($stitem->description, 0, 60));
			$this->refresh();
			return;
		} else {
			$db->CompleteTrans();
			$flash->addMessage('Transfer completed successfully');
		}

		if (isset($this->_data['saveAnother'])) {
			$this->_data['whaction_id']=$data['whaction_id'];
			$_POST[$this->modeltype]['qty']='';
			$_POST[$this->modeltype]['balance']='';
			$this->refresh();
		} else {
			sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}

	}

	/*
	 * Ajax Functions
	 */
	public function getUoM($_stitem_id='') {
	// used by ajax to get the UoM

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['stitem_id'])) { $_id=$this->_data['stitem_id']; }
		}

		$stitem=new STItem();
		$stitem->load($_stitem_id);

		if(isset($this->_data['ajax'])) {
			$this->view->set('value',$stitem->uom_name);
			$this->setTemplateName('text_inner');
		} else {
			return $stitem->uom_name;
		}
	}

	public function getUomList($_stitem_id='') {
	// used by ajax to get the UoM

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
		}

		$stitem = new STItem();
		$stitem->load($_stitem_id);

		$list=$stitem->getUomList();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$list);
			$this->setTemplateName('select_options');
		} else {
			return $list;
		}

	}

	public function getBalance($_stitem_id='',$_location_id='',$_bin_id='') {
// Function called by Ajax Request to return balance for selected item, location, bin
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
			if(!empty($this->_data['whlocation_id'])) { $_location_id=$this->_data['whlocation_id']; }
			if(!empty($this->_data['whbin_id'])) { $_bin_id=$this->_data['whbin_id']; }
		}

		$balance=new STBalance();
		$cc=new ConstraintChain();
		$cc->add(new Constraint('stitem_id', '=', $_stitem_id));
		$cc->add(new Constraint('whlocation_id', '=', $_location_id));
		if (!empty($_bin_id) && $_bin_id!="null") {
			$cc->add(new Constraint('whbin_id', '=', $_bin_id));
		}
		$balance->loadBy($cc);
		$balances=($balance->isLoaded())?$balance->balance:0;
		if(isset($this->_data['ajax'])) {
			$this->view->set('value',$balances);
			$this->setTemplateName('text_inner');
		} else {
			return $balances;
		}

	}

	public function getBalancesBinList($_stitem_id='', $_whlocation_id='') {
	// used by ajax to get a list of bins for a location

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['stitem_id'])) { $_stitem_id=$this->_data['stitem_id']; }
		}
		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['whlocation_id'])) { $_whlocation_id=$this->_data['whlocation_id']; }
		}

		$location=New WHLocation();
		$location->load($_whlocation_id);
		$bins=$location->getBinList();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$bins);
			$this->setTemplateName('select_options');
		} else {
			return $bins;
		}
	}

	public function getBinList($_whlocation_id='') {
	// used by ajax to get a list of bins for a location

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['whlocation_id'])) { $_whlocation_id=$this->_data['whlocation_id']; }
		}

		$location=New WHLocation();
		$location->load($_whlocation_id);
		$bins=$location->getBinList();

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$bins);
			$this->setTemplateName('select_options');
		} else {
			return $bins;
		}
	}

	public function getFromLocations($_whaction_id='') {
	// used by ajax to get a list of locations for a given WH Action and From Location

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['whaction_id'])) { $_whaction_id=$this->_data['whaction_id']; }
		}

		$transfer_rule=New WHTransferrule();
		$locations=$transfer_rule->getFromLocations($_whaction_id);

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}

	}

	public function getToLocations($_whlocation_id='',$_whaction_id='') {
	// used by ajax to get a list of locations for a given WH Action and From Location

		if(isset($this->_data['ajax'])) {
			if(!empty($this->_data['whlocation_id'])) { $_whlocation_id=$this->_data['whlocation_id']; }
			if(!empty($this->_data['whaction_id'])) { $_whaction_id=$this->_data['whaction_id']; }
		}

		$transfer_rule=New WHTransferrule();
		$locations=$transfer_rule->getToLocations($_whaction_id, $_whlocation_id);

		if(isset($this->_data['ajax'])) {
			$this->view->set('options',$locations);
			$this->setTemplateName('select_options');
		} else {
			return $locations;
		}

	}

	/**
	 * Return the module (system) preferences
	 *
	 * @return array
	 */
	public static function getPreferences() {
	    // Get module preferences
	    $system_prefs = SystemPreferences::instance();
	    $module_prefs = $system_prefs->getModulePreferences('manufacturing');

	    // Fix any empty prefs
	    if (! isset($module_prefs['default-operation-units'])) {
	        $module_prefs['default-operation-units'] = 'H';
	    }
	    if (! isset($module_prefs['default-cost-basis'])) {
	        $module_prefs['default-cost-basis'] = 'VOLUME';
	    }
	    if (! isset($module_prefs['use-only-default-cost-basis'])) {
	        $module_prefs['use-only-default-cost-basis'] = 'on';
	    }
	    return $module_prefs;
	}

	/*
	 * Protected Functions
	 */
	protected function save_model ($modelName, $dataIn=array(),&$errors=array()) {
		return parent::save($modelName, $dataIn, $errors);
	}

}
?>