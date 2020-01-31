<?php

/** 
 *	Person Model
 *	
 *	@author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */

class Person extends Party
{
	
	protected $version = '$Revision: 1.17 $';
	
	protected $defaultDisplayFields = array('name'			=> 'Name'
										   ,'company'			=> 'Company'
										   ,'accountnumber'	=> 'Account'
										   ,'jobtitle'		=> 'Job Title'
										   ,'phone'			=> 'Phone'
										   ,'mobile'			=> 'Mobile'
										   ,'email'			=> 'Email'
										   );
	
	function __construct($tablename = 'person')
	{
// Register non-persistent attributes
		
// Contruct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->idField	= 'id';
		$this->subClass	= true;
		$this->fkField	= 'party_id';
		
		$this->orderby = array('surname', 'firstname');

		$this->identifier			= 'surname';
		$this->identifierField		= ['firstname', 'surname'];
		$this->identifierFieldJoin	= ' ';
		
// Define relationships
		$this->hasMany('PartyContactMethod','contactmethods', 'party_id', 'party_id', null, TRUE);
		$this->hasMany('PartyAddress','addresses', 'party_id', 'party_id', null, TRUE);
		$this->hasMany('PartyAddress','mainaddress', 'party_id', 'party_id');
 		
		$this->belongsTo('Company', 'company_id', 'company');
 		$this->belongsTo('User', 'alteredby', 'last_altered_by');
 		$this->belongsTo('User', 'assigned_to', 'person_assigned_to');
		
		$this->hasOne('Party', 'party_id', 'party');
		$this->hasOne('Company', 'company_id', 'companydetail');
		
 		$this->actsAsTree('reports_to');
		$this->belongsTo('Person', 'reports_to', 'person_reports_to', null, 'surname || \', \' || firstname');
 		$this->belongsTo('Language', 'lang', 'language');
		$this->setConcatenation('fullname',array('title', 'firstname', 'middlename', 'surname', 'suffix'));
		$this->setConcatenation('titlename',array('title', 'firstname', 'surname'));
		
		$this->hasMany('Opportunity', 'opportunities');
		$this->hasMany('Project', 'projects');
		$this->hasMany('Activity', 'activities');
		
// Define field formats
		$this->getField('jobtitle')->tag = prettify('job_title');
		
// Define validation
		
// Define default values
		
// Define enumerated types
		
	}
	
	/*
	 * getByCompany - gets people by company
	 *
	 * @param $_current boolean true (default) only gets current people
	 * @param $_company_id company id to get people for, default is system company
	 * @return array list of person_id, name pairs
	 * 
	 */
	function getByCompany($_current = TRUE, $_company_id = COMPANY_ID)
	{
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company_id', '=', $_company_id));
		
		if ($_current)
		{
			$this->getCurrent($cc);
		}
		
		return $this->getAll($cc);
	}
	
	function getCurrent($cc = null)
	{
		
		if (!($cc instanceof ConstraintChain))
		{
			$cc = new ConstraintChain();
		}
		
		$cc1 = new ConstraintChain();
		
		$cc1->add(new Constraint('end_date', 'is', 'NULL'));
		$cc1->add(new Constraint('end_date', '>', fix_date(date(DATE_FORMAT))), 'OR');
		
		$cc->add($cc1);
		
	}
	
}

// End of Person
