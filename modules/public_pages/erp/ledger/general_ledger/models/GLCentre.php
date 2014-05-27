<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class GLCentre extends DataObject {

	protected $version='$Revision: 1.10 $';
	
	protected $linkRules;
										 
	public function __construct($tablename='gl_centres') {
		parent::__construct($tablename);
		$this->identifierField = 'cost_centre || \' - \' || description';
		$this->hasMany('GLAccountCentre','accounts', 'glcentre_id');	
		$this->orderby = 'cost_centre';
		$this->validateUniquenessOf('cost_centre');	
		$sh=new SearchHandler(new GLAccountCentreCollection(new GLAccountCentre), false);
		$sh->setOrderby('glaccount');
		$this->addSearchHandler('accounts', $sh);
	
// Define related item rules
  		$this->linkRules=array('accounts'=>array('actions'=>array()
													,'rules'=>array())
							  );
	}

	public function getAccounts() {
 		$accounts=array();
 		foreach ($this->accounts as $account) {
 			$accounts[$account->glaccount_id]=$account->glaccount;
 		}
		return $accounts;
	}
	
 	public function getAccountIds() {

 		$account_ids=array();
 		foreach ($this->accounts as $account) {
 			$account_ids[]=$account->glaccount_id;
 		}
		return $account_ids;
 	}
 
}
?>