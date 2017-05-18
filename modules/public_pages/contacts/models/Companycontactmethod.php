<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Companycontactmethod extends DataObject
{
	
	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array('name'		=> 'Name'
										   ,'contact'	=> 'Contact'
										   ,'main'		=> 'Main'
										   ,'billing'	=> 'Billing'
										   ,'shipping'	=> 'Shipping'
										   ,'payment'	=> 'Payment'
										   ,'technical'	=> 'Technical'
										   );
	
	function __construct($tablename = 'company_contact_methods')
	{
		parent::__construct($tablename);
		
		$this->idField = 'id';
		
 		$this->belongsTo('Company', 'company_id', 'company'); 

	}
	
	function __toString()
	{
		$value=$this->contact;
		
		return (!empty($value)?$value:'');
	}

}

// End of Companycontactmethod
