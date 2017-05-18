<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class TicketsSearch extends BaseSearch {

	protected $version='$Revision: 1.7 $';
	
	public static function useDefault($search_data=null, &$errors=array(), $defaults=null) {
		$search = new TicketsSearch($defaults);
		$search->addSearchField(
			'id',
			'ticket_#',
			'equal'
		);
		$search->addSearchField(
			'internal_status_code',
			'status_is',
			'ticket_status',
			array('NEW','OPEN')
		);
		$user = new User();
		$user->loadBy('username', EGS_USERNAME);
		$search->addSearchField(
			'originator_person_id',
			'my_tickets_only',
			'hide',
			false,
			'advanced'
		);
		$search->setOnValue('originator_person_id',$user->username);
		$search->addSearchField(
			'summary',
			'summary_contains',
			'contains'
		);
		$search->addSearchField(
			'assigned_to',
			'assigned_to',
			'select',
			''
		);
		$options=array(''=>'all',EGS_USERNAME=>'me','NULL'=>'noone');
		
		if(isModuleAdmin()) {
			$users = User::getOtherUsers();
			$options=array_merge($options,$users);
		}
		$search->setOptions('assigned_to',$options);
		$search->addSearchField(
			'originator_company',
			'company_name',
			'begins',
			null,
			'advanced'
		);
		$search->addSearchField(
			'created',
			'created_today',
			'hide',
			false,
			'advanced'
		);
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('created','>',date('Y-m-d',strtotime('yesterday'))));
		$cc->add(new Constraint('created','<',date('Y-m-d',strtotime('tomorrow'))));
		$search->setConstraint('created',$cc);

		$queue=new TicketQueue();
		$queues=$queue->getAll();
		$search->addSearchField(
			'ticket_queue_id',
			'queue',
			'multi_select',
			array_keys($queues),
			'advanced'
		);
		$search->setOptions('ticket_queue_id',$queues);
		
		$search->addSearchField(
			'ticket_release_version_id',
			'release_version',
			'select',
			'',
			'advanced'
		);
		$releaseversion=new TicketReleaseVersion();
		$releaseversions=$releaseversion->getAll();
		$options=array(''=>'All');
		$options+=$releaseversions;
		$search->setOptions('ticket_release_version_id', $options);
		
 		$search->setSearchData($search_data,$errors);
		return $search;
	}
	
	public static function useClient($search_data=null, &$errors, $defaults=null) {
		$search=self::useDefault($search_data, $errors, $defaults);
		$search->removeSearchField('assigned_to');
		$search->removeSearchField('originator_company');
		return $search;
	}
		
	public static function mytickets() {
		$search = new TicketsSearch();
		$search->setHidden();
		$field = new SelectSearchField('originator_person_id');
		$user = new User();
		$user->loadBy('username', EGS_USERNAME);
		$field->setValue($user->username);
		$search->addField('originator_person_id',$field);
		return $search;
	}
	
}
?>