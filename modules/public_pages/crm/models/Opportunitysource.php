<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Opportunitysource extends DataObject {

	function __construct($tablename='opportunitysource') {
		parent::__construct($tablename);
		$this->idField='id';

		$this->orderby='position';
 		$this->validateUniquenessOf('id');
 		$this->belongsTo('Company', 'companyid', 'companyid');

	}

}
?>