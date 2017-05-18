<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class RecentlyAddedCompaniesEGlet extends SimpleListEGlet {
	
	function populate() {
		if(!$this->isCached()) {
			$pl = new PageList('recently_added_companies');
			$companies_do = new CompanyCollection(new Company);
			$sh = new SearchHandler($companies_do,false);
			$sh->extract();
			$sh->addConstraint(new Constraint('is_lead','=','false'));
			$sh->setLimit(10);
			$sh->setOrderBy('created','DESC');
			
			$companies_do->load($sh);
			$pl->addFromCollection($companies_do,array('module'=>'contacts','controller'=>'companys','action'=>'view'),array('id'),'company','name');
			$this->setCache($pl->getPages()->toArray());
		}
		$this->contents = $this->getCache();
	}
	
}
?>
