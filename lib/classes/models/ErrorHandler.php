<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ErrorHandler extends DataObject {

	public function __construct() {
		parent::__construct('system_errors');
		$this->idField='id';
		
		$this->orderby=array('lastupdated', 'id');
		$this->orderdir=array('DESC', 'DESC');
		$this->setEnum('status',array('N'=>'New'
                                 	 ,'C'=>'Closed'));
		$this->getField('status')->setDefault('N');        
		
	}

	function &Instance() {
		static $errorhandler = NULL;
		if (!$errorhandler) {
			$errorhandler=new ErrorHandler();
		}
		return $errorhandler;
	}

	public function save($errno, $errstr, $errfile, $errline) {
		$db=DB::Instance();
		$db->CompleteTrans();

		$errors=array();
		$flash=Flash::Instance();
		
		$data= array('error_number'=>$errno
					,'error_message'=>$errstr
					,'error_file'=>$errfile
					,'error_line'=>$errline);
					
		$error=DataObject::Factory($data, $errors, 'ErrorHandler');
		
		if (!$error || count($errors)>0 || !$error->save()) {
			$flash->addError('System Error - '.$errstr.' in '.$errfile.' at '.$errline);
		} else {
			$flash->addError('System Error (Ref:'.$error->id.' - '.$errstr);
		}
		return false;
	}
	
}

?>
