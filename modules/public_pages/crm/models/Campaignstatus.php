<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Campaignstatus extends DataObject {

	function __construct($tablename='campaignstatus') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->view='';
		$this->orderby='position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');

	}

}
?>