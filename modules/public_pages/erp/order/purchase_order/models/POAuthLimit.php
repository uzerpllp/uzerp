<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class POAuthLimit extends DataObject
{

	protected $version = '$Revision: 1.11 $';
	
	protected $defaultDisplayFields = array('username'=>'Person'
											,'cost_centre'
											,'order_limit');
	
	protected $linkRules;
											
	function __construct($tablename = 'po_auth_limits')
	{
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'username || : || cost_centre';
		$this->orderby			= 'username';
		
// Define relationships
 		$this->belongsTo('GLCentre', 'glcentre_id', 'cost_centre');
		$this->belongsTo('User', 'username', 'person');
  		$this->hasMany('POAuthAccount', 'authaccounts', 'po_auth_limit_id', null, true);
 		
// Define validation rules
  		$this->validateUniquenessOf(array('username', 'glcentre_id'));
  		
// Define field formats
 		
// Define enumerated types
 		
// Define related item rules
  		$this->linkRules = array('authaccounts'=>array('actions'=>array()
													  ,'rules'=>array())
								);
	}

	public function getAccountIds()
	{

 		$account_ids = array();
 		
 		foreach ($this->authAccounts as $account)
 		{
 			$account_ids[$account->glaccount_id] = $account->glaccount_id;
 		}
 		
		return $account_ids;
 	}
	
 	public static function getUnassignedCentres($_username)
 	{

 		$cc = new ConstraintChain();
 		
 		$cc->add(new Constraint('username', '=', $_username));
 		
 		$poauthlimit = DataObjectFactory::Factory('POAuthLimit');
 		
 		$poauthlimit->idField			= 'glcentre_id';
 		$poauthlimit->identifierField	= 'glcentre_id';
 		
 		$assigned_centres = $poauthlimit->getAll($cc);

 		$centres = DataObjectFactory::Factory('GLCentre');
 		
		$cc = new ConstraintChain();
		
		if (!empty($assigned_centres))
		{
	 		$cc->add(new Constraint('id', 'not in', '('.implode(',', $assigned_centres).')'));
		}
		return 	$centres->getAll($cc);
		
 	}
 	
}

// End of POAuthLimit
