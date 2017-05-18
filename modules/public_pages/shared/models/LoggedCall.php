<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class LoggedCall extends DataObject {
	
	protected $defaultDisplayFields = array('subject'
										  ,'direction'
										  ,'company'
										  ,'person'
										  ,'duration'
										  ,'start_time'
										  ,'end_time'
										  ,'owner'
										  );
	
	function __construct($tablename='logged_calls') {
		parent::__construct($tablename);
 		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('Person', 'person_id', 'person'); 
		$this->belongsTo('Project', 'project_id', 'project'); 
		$this->belongsTo('Opportunity', 'opportunity_id', 'opportunity'); 
		$this->belongsTo('Activity', 'activity_id', 'activity'); 
		$this->getField('end_time')->setDefault(strtotime('+10 minutes'));
		$this->setEnum('direction',array('IN'=>'Incoming','OUT'=>'Outgoing'));
		$this->orderby='end_time';
		$this->actsAsTree('parent_id');
		$this->setParent();
		$this->setAdditional('duration','interval');
		$this->identifierField='subject';
	}
	
}
?>