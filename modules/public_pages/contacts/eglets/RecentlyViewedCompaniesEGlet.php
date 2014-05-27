<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class RecentlyViewedCompaniesEGlet extends SimpleListEGlet {
	
	function populate() {
		$pl = new PreferencePageList('recently_viewed_companies'.EGS_COMPANY_ID);
		$this->contents=$pl->getPages()->toArray();
	}
	
}
?>
