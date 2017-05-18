<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class AddresslessCustomerDetailsEditor implements EditingCustomerDetails {
	protected $data=array();
	protected $customer;
	protected $controller;
	
	function Execute(Array $data,Controller $controller) {
		
		$this->controller=$controller;
		$this->data=$data;
		$this->customer=new Customer();
		$this->person=new Person();
		$this->loadCustomer();
		$this->loadPerson();
		
		//if the customer is trying to change their password
		if($this->passwordIsBeingChanged()) {
			//first need to check the password
			if(!$this->checkCurrentPassword()) {
				$this->currentPasswordCheckFailed();
				return;
			}
			if(!$this->checkNewPasswordMatch()) {
				$this->newPasswordMatchFailed();
				return;
			}
			
			$this->customer->password=md5($this->data['Customer']['new_password']);
			unset($this->data['Customer']['password']);
			unset($this->data['Customer']['new_password']);
			unset($this->data['Customer']['confirmation_password']);
		}
		
		$result=$this->customer->updateEmailAddress($this->data['Person']['email']['contact']);
		$result=$result&&$this->customer->updatePhoneNumber($this->data['Person']['phone']['contact']);
		if($result!==false&&$this->customer->save()) {
			$flash=Flash::Instance();
			$flash->addMessage('Details saved successfully');
			sendTo('shop/customer/details');
		}
		
	}
	protected function loadCustomer() {
		$this->customer->load($_SESSION['customer_id']);
	}
	
	protected function loadPerson() {
		$this->person->load($this->customer->person_id);
	}
	
	protected function checkCurrentPassword() {
		if(empty($this->data['Customer']['password'])||md5($this->data['Customer']['password'])!==$this->customer->password) {
			return false;
		}
		return true;
	}
	
	protected function currentPasswordCheckFailed() {
		$flash=Flash::Instance();
		$flash->addError('You must enter your current password correctly to make any changes','password');
		sendTo('shop/customer/details');
	}
	
	protected function passwordIsBeingChanged() {
		if(!empty($this->data['Customer']['new_password']))
			return true;
		return false;
	}
	
	protected function checkNewPasswordMatch() {
		if(empty($this->data['Customer']['confirmation_password'])||$this->data['Customer']['new_password']!==$this->data['Customer']['confirmation_password'])
			return false;
		return true;
	}
	
	protected function newPasswordMatchFailed() {
		$flash=Flash::Instance();
		$flash->addError('You must enter the new password twice in order to change it- the two entries didn\'t match','confirmation_password');
		sendTo('shop/customer/details');
	}
}

?>