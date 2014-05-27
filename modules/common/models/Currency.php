<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Currency extends DataObject {

	protected $version = '$Revision: 1.5 $';
	
	protected $defaultDisplayFields = array(
		'currency'		=> 'Currency',
		'description'	=> 'Description',
		'symbol'		=> 'Symbol',
		'decdesc'		=> 'decimal description',
		'rate'			=> 'rate',
		'writeoff'		=> 'Writeoff Account',
		'glcentre'		=> 'Cost Centre',
		'datectrl'		=> 'Date Control',
		'method'		=> 'method',
	);
	
	function __construct($tablename = 'cumaster')
	{
// Register non-persistent attributes
		
// Construct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'currency';
		
// Set ordering attributes
		$this->orderby			= 'currency';
		
// Define validation
		$this->validateUniquenessOf('currency');
		$this->getField('writeoff_glaccount_id')->addValidator(new PresenceValidator());
		$this->getField('glcentre_id')->addValidator(new PresenceValidator());
		$this->addValidator(new fkFieldCombinationValidator('GLAccountCentre', array('writeoff_glaccount_id'=>'glaccount_id', 'glcentre_id'=>'glcentre_id')));
		
// Define relationships
 		$this->belongsTo('GLAccount', 'writeoff_glaccount_id', 'writeoff');
		$this->belongsTo('GLCentre', 'glcentre_id', 'glcentre'); 
		
// Define field formats
		
// Define enumerated types
		$this->setEnum('method',array('D'=>'Divide','M'=>'Multiply'));
		
// Define system defaults
		
// Define related item rules
	
	}

}

// end of Currency.php