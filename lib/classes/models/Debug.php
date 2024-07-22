<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Debug extends DataObject {

	private $debugoptions=array();
	
	function __construct($tablename='debug_header') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->hasMany('Debuglines', 'lines', 'debug_id');
		
	}

	static function &Instance($type=null) {
		static $debug;
		if ($debug==null) {
			$debug=new Debug();
			$result=false;
			$result=$debug->loadBy('sessionid',session_id());
			if($result===false) {
				$data=array('sessionid'=>session_id()
						   ,'username'=>''
						   ,'customer_id'=>'');
				if (isset($_SESSION['username'])) {
					$data['username']=$_SESSION['username'];
				}
				if (isset($_SESSION['customer_id'])) {
					$data['customer_id']=$_SESSION['customer_id'];
				}
				$errors=array();
				$debug=Debug::Factory($data,$errors,'Debug');
				$debug->save();
			}
			$debug->setDebugOptions();
		}
		return $debug;
	}

	function update($id, $fields, $values) {
		if(isLoggedIn()) {
			if (isset($_SESSION['customer_id'])) {
				$this->customer_id=$_SESSION['customer_id'];
			}
			if (isset($_SESSION['username'])) {
				$this->username=$_SESSION['username'];
			}
		} else {
			$this->customer_id=-1;
			$this->username='';
		}
		$this->save();
	}
	
	function write ($msg,$newline=true) {
		if (count($this->debugoptions)==0) {
			$this->saveline($msg,$newline);
		} else {
			foreach ($this->debugoptions as $option) {
				if (strpos((string) $msg, (string) $option)===0) {
					$this->saveline($msg,$newline);
				}
			}
		}
	}

	private function saveline($msg,$newline=true) {
		$data=array('debug_id'=>$this->id
				   ,'line'=>$msg);
		$errors=array();
		$debugline=Debuglines::Factory($data, $errors, 'Debuglines');
		if ($debugline) {
			$debugline->save();
		}
		
	}
	
	private function setDebugOptions () {
		$debugoption=DebugOption::getDebugOption();
		$definedoptions=$debugoption->getOptions();
		$debugoptions=$debugoption->getEnumOptions('options');
		foreach ($definedoptions as $option) {
			$this->debugoptions[$option]=$debugoptions[$option];
		}
	}
	
}
?>