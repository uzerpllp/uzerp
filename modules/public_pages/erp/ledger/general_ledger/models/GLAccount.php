<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLAccount extends DataObject {
	
	protected $version='$Revision: 1.10 $';
	
	protected $defaultDisplayFields=array('account'=>'Account'
										 ,'description'=>'Description'
										 ,'actype'=>'Account type'
										 ,'control'=>'control'
										 ,'analysis'=>'analysis'
										 );

	protected $linkRules;
										 
	function __construct($tablename='gl_accounts') {
// Register non-persistent attributes

// Contruct the object
		parent::__construct($tablename);

// Set specific characteristics
		$this->identifierField = 'account || \' - \' || description';

// Set ordering attributes
		$this->orderby='account';

// Define validation
		$this->validateUniquenessOf('account');
		
// Define relationships
		$this->belongsTo('GLAnalysis','glanalysis_id','analysis');
		$this->hasMany('GLAccountCentre','centres','glaccount_id');
		
		$sh=new SearchHandler(new GLAccountCentreCollection(new GLAccountCentre()), false);
		$sh->setOrderby('glcentre');
		$this->addSearchHandler('centres', $sh);

// Define field formats
		
// Define enumerated types
		$this->setEnum('actype',array('P'=>'Profit & Loss','B'=>'Balance Sheet'));
		
// Define system defaults
		
// Define related item rules
  		$this->linkRules=array('centres'=>array('actions'=>array()
													,'rules'=>array())
							  );
	}

 	public function getCentres() {
  		$centres=array();
 		foreach ($this->centres as $centre) {
  			$centres[$centre->glcentre_id]=$centre->glcentre;
 		}
		return $centres;
 		
 	}
 	
 	
  	public function getCentreIds() {
  		$centre_ids=array();
 		foreach ($this->centres as $centre) {
 			$centre_ids[$centre->glcentre_id]=$centre->glcentre_id;
 		}
		return $centre_ids;
  	}

	public function getEnumValue($field) {
		return $this->_fields[$field]->value;
	}

 	function nonControlAccounts() {

		$cc=new ConstraintChain();
		$cc->add(new Constraint('control', 'is not', 'true'));
		return $this->getAll($cc);
	}
 	
 	function getIdentifier() {
 	     return 'account || \' - \' || description';
 	}

}
?>