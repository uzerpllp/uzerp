<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class CurrentActivitiesEGlet extends SimpleListEGlet {
	
	function populate() {
		$pl = new PageList('current_activities');
		$current_activities = new ActivityCollection(new Activity);
		$sh = new SearchHandler($current_activities,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('assigned','=',EGS_USERNAME));
		$sh->addConstraint(new Constraint('completed','IS','NULL'));
		$sh->addConstraint(new Constraint('startdate','<','(now())'));
		$sh->setLimit(10);
		$sh->setOrderBy('created','DESC');
		$current_activities->load($sh);
		$pl->addFromCollection($current_activities,array('module'=>'crm','controller'=>'activitys','action'=>'view'),array('id'),'activity','name');
		$this->contents=$pl->getPages()->toArray();
	}
	
}
?>
