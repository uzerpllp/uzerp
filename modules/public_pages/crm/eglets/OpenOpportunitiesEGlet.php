<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class OpenOpportunitiesEGlet extends SimpleListEGlet {
	
	function populate() {
		$pl = new PageList('open_opportunities');
		$open_opportunities = new OpportunityCollection(new Opportunity);
		$sh = new SearchHandler($open_opportunities,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('owner','=',EGS_USERNAME));
		$sh->addConstraint(new Constraint('open','=','true'));
		$sh->setLimit(10);
		$sh->setOrderBy('cost','DESC');
		$open_opportunities->load($sh);
		$pl->addFromCollection($open_opportunities,array('module'=>'crm','controller'=>'opportunitys','action'=>'view'),array('id'),'opportunity','name');
		$this->setData($pl->getPages()->toArray());
	}
	
}
?>