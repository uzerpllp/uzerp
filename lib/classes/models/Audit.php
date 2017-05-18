<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Audit extends DataObject
{

	protected $version='$Revision: 1.12 $';
	
	function __construct($tablename='audit_header')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField='id';

		// Define relationships
		
		// Define field formats
		
		// Define validation
		
		// Define enumerated types
		
		// Define default values
		
		// Define link rules for related items
		
	}

	static function &Instance($type=null)
	{
		static $audit = NULL;
		
		if (!$audit)
		{
			$audit = new Audit();
			
			$result = false;
			
			$result = $audit->loadBy('sessionid',session_id());
			
			if($result===false)
			{
				$data=array('sessionid'=>session_id()
						   ,'username'=>''
						   ,'customer_id'=>'');
				
				if (isset($_SESSION['username']))
				{
					$data['username'] = $_SESSION['username'];
				}
				
				if (isset($_SESSION['customer_id']))
				{
					$data['customer_id'] = $_SESSION['customer_id'];
				}
				
				$errors = array();
				
				$audit = Audit::Factory($data,$errors,'Audit');
				
				$audit->save();
			}
		}
		
		if (isset($_SESSION['username']) && $audit->username != $_SESSION['username'])
		{
			$audit->username = $_SESSION['username'];
			
			$audit->save();
		}
		
		return $audit;
	}

	function update()
	{
		if(isLoggedIn())
		{
			if (isset($_SESSION['customer_id']))
			{
				$this->customer_id=$_SESSION['customer_id'];
			}
			
			if (isset($_SESSION['username']))
			{
				$this->username=$_SESSION['username'];
			}
//		} else {
//			$this->customer_id=-1;
//			$this->username='';
		}
		
		$this->save();
	}
	
	function write ($msg, $newline=true, $elapsed_time=0)
	{
		$data=array('audit_id'			=> $this->id
				   ,'username'			=> $this->username
				   ,'line'				=> $msg
				   ,'remote_address'	=> $_SERVER['REMOTE_ADDR']
				   ,'user_agent'		=> $_SERVER['HTTP_USER_AGENT']
				   ,'referer'			=> (empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'])
				   ,'elapsed_time'		=> $elapsed_time
				   ,'memory_used'		=> memory_get_usage());
		
		$errors=array();
		
		$auditline=Auditlines::Factory($data, $errors, 'Auditlines');
		
		if ($auditline)
		{
			$auditline->save();
		}
	}

}

// End of Audit
