<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Calendar extends DataObject {

	protected $defaultDisplayFields = array('id','name','type','owner','colour');

	function __construct($tablename='calendars') {
		parent::__construct($tablename);
		$this->idField='id';
		$this->hasMany('CalendarShare','calendarshare','calendar_id');
		
	 	$this->setEnum('type'
				,array('gcal'=>'Google Calendar'
					  ,'group'=>'Group Calendar'
					  ,'personal'=>'Personal Calendar'
					)
	 		);
		
	 	$this->setEnum('colour'
				,array('#A32929',
					   '#B1365F',
					   '#7A367A',
					   '#5229A3',
					   '#29527A',
					   '#2952A3',
					   '#1B887A',
					   '#28754E',
					   '#0D7813',
					   '#528800',
					   '#88880E',
					   '#AB8B00',
					   '#BE6D00',
					   '#B1440E',
					   '#865A5A',
					   '#705770',	
					   '#4E5D6C',
					   '#5A6986',
					   '#4A716C',
					   '#6E6E41',
					   '#8D6F47'
					)
	 		);
	}
	
	/**
	 * getWritableCalendars
	 * 
	 * Used to return an array of calendars that a specific user has write access to
	 * This will automatically exclude gcal until write functionality has been developed
	 *
	 * @return array of writable calendars
	 */
	function getWritableCalendars($calendar_id='') {
		$calendar=new CalendarCollection($this);
		$sh = new SearchHandler($calendar, false);
		if(!empty($calendar_id)) {
			$sh->addConstraint(new Constraint('id', '=', $calendar_id));
		}
		$sh->addConstraint(new Constraint('type', '!=', 'gcal'));
		$sh->addConstraint(new Constraint('owner', '=', EGS_USERNAME));
		$cc = new ConstraintChain();
		$cc->add(new Constraint('type', '=', 'group'));
		$cc->add(new Constraint('username', '=', EGS_USERNAME));
		$sh->addConstraintChain($cc,'OR');
		$sh->setOrderby('name');
		$sh->setGroupBy('id');
		$calendar->load($sh);
		$calendar_id=array();
		foreach($calendar as $key=>$value) {
			$calendar_id[$value->id]=$value->name . " (".$value->owner.")";
		}
		return $calendar_id;
	}
	
	
	/**
	 * isOwner
	 * 
	 * Used to check if the current user is the owner of the specified calendar_id
	 *
	 * @return boolean
	 */
	function isOwner($calendar_id) {
		$calendar=new Calendar();
		$calendar->load($calendar_id);
		if($calendar->owner==EGS_USERNAME) {
			return true;
		} else {
			return false;
		}
	}
	
	function isWritable($calendar_id) {
		$calendars=$this->getWritableCalendars($calendar_id);
		
		if(array_key_exists($calendar_id,$calendars)) {
			return true;
		} else {
			return false;
		}
	}
	
	function getSharedIds() {
		// retrieve calendar data
		$calendar=new CalendarShareCollection(new CalendarShare());
		$sh = new SearchHandler($calendar, false);
		$sh->addConstraint(new Constraint('username', '=', EGS_USERNAME));
		$calendar->load($sh);
		$calendar_id=array();
		foreach($calendar as $key=>$value) {
			$calendar_id[$value->calendar_id]=$value->calendar_id;
		}
		return $calendar_id;
	}
}

?>