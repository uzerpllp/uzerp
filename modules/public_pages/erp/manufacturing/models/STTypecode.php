<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class STTypecode extends DataObject
{

	protected $version='$Revision: 1.6 $';
	
	protected $defaultDisplayFields = array('type_code'
											,'description'
											,'backflush_action'
											,'complete_action'
											,'issue_action'
											,'backflush_action_id'
											,'complete_action_id'
											,'issue_action_id'
											);
	
	function __construct($tablename='st_typecodes')
	{
		parent::__construct($tablename);
		$this->idField='id';
		$this->orderby='type_code';
		
		$this->identifierField=array('type_code', 'description');
		
		$this->validateUniquenessOf('type_code'); 
		
		$this->belongsTo('WHAction', 'backflush_action_id', 'backflush_action');
		$this->belongsTo('WHAction', 'complete_action_id', 'complete_action');
		$this->belongsTo('WHAction', 'issue_action_id', 'issue_action');
		
	}

}

// End of STTypecode
