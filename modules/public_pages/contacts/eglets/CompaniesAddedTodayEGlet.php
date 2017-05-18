<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CompaniesAddedTodayEGlet extends SimpleListEGlet {
	
	function populate() {
		$pl = new PageList('companies_added_today');
		$companies_do = new CompanyCollection(new Company);
		$sh = new SearchHandler($companies_do,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('is_lead','=','false'));
		$sh->addConstraint(new Constraint('created','>',fix_date(date(DATE_FORMAT))));
		$sh->setLimit(10);
		
		$companies_do->load($sh);
		$pl->addFromCollection($companies_do,array('module'=>'contacts','controller'=>'companys','action'=>'view'),array('id'),'company','name');
		$this->contents=$pl->getPages()->toArray();
	}
	
}
?>
