<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Company extends Party {
	
	protected $version = '$Revision: 1.30 $';
	
	protected $defaultDisplayFields = array('name'
										   ,'accountnumber'
										   ,'town'
										   ,'phone'
										   ,'email'
										   ,'website');
	
	function __construct($tablename = 'company')
	{
		
		// Register non-persistent attributes

		// Construct the object
		parent::__construct($tablename);
		$this->idField			= 'id';

		// Set specific characteristics
		$this->subClass			= true;
		$this->fkField			= 'party_id';
		$this->orderby			= 'name';
		$this->identifier		= 'name';
		$this->identifierField	= 'name';

		// Define validation
		$this->validateUniquenessOf('accountnumber');
		$this->validateUniquenessOf('name');
		
		// Define relationships
 		$this->hasMany('PartyContactMethod', 'contactmethods', 'party_id', 'party_id');
		$this->hasMany('PartyAddress', 'addresses', 'party_id', 'party_id');
		$this->hasMany('PartyAddress', 'mainaddress', 'party_id', 'party_id');
 		
 		$this->belongsTo('User', 'assigned', 'assigned_to');
		$this->belongsTo('Company', 'parent_id', 'company_parent');
		$this->belongsTo('CompanyClassification','classification_id', 'company_classification');
		$this->belongsTo('CompanyIndustry', 'industry_id', 'company_industry');
		$this->belongsTo('CompanyRating', 'rating_id', 'company_rating');
		$this->belongsTo('CompanySource', 'source_id', 'company_source');
		$this->belongsTo('CompanyStatus', 'status_id', 'company_status');
		$this->belongsTo('CompanyType', 'type_id', 'company_type');
		$this->addValidator(new DistinctValidator(array('id', 'parent_id'), 'Account cannot be it\'s own parent'));
 		$this->actsAsTree('parent_id');
		$this->setParent();

		$this->hasOne('Party', 'party_id', 'party');
		
		$this->hasMany('Person', 'people');
		$this->hasMany('Opportunity', 'opportunities');
		$this->hasMany('Project', 'projects');
		$this->hasMany('Activity', 'activities');
		$this->hasMany('CompanyInCategories', 'categories');
		
		// Define field formats
		$this->getField('website')->setFormatter(new URLFormatter());
		
		$this->getField('website')->type = 'html';
		
		$system_prefs = SystemPreferences::instance();
		$autoGenerate = $system_prefs->getPreferenceValue('auto-account-numbering', 'contacts');
		
		if(!(empty($autoGenerate) && $autoGenerate === 'on'))
		{
			//$this->getField('accountnumber')->not_null=false;
			$this->_autohandlers['accountnumber'] = new AccountNumberHandler();
		}
		else
		{
			$this->getField('accountnumber')->setnotnull();
		}

	}

	public function createAccountNumber($companyname = null)
	{
		if (empty($companyname))
		{
			$companyname = $this->name;
		}
		
		// Make an acronym based on the name
		$letters = array();
		
		$words = explode(' ', $companyname);
		
		$len = 1;
		
		if(count($words)<3) $len=2;
		
		foreach($words as $word)
		{
			$word = (substr($word, 0, $len));
			array_push($letters, $word);
		}
		
		$accnum = strtoupper(implode($letters));
		
		// Now add a number to the end until an untaken one is found
		$i = 1;
		
		$testaccnum = $accnum.sprintf("%02s",$i);
		
		while(!$this->isValidAccountNumber($testaccnum))
		{
			$i++;
			$testaccnum = $accnum.sprintf("%02s",$i);
		}
		return $testaccnum;

	}

	public function getPeople ($_person='')
	{

		$person = DataObjectFactory::Factory('Person');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('company_id', '=', $this->id));
		
		if (!empty($_person))
		{
			$cc->add(new Constraint("lower(surname) || ', ' || lower(firstname)", 'like', strtolower($_person).'%'));
		}
		
		$person->identifierField="surname || ', ' || firstname";
		
		$people = $person->getAll($cc, true);
		
		asort($people);
		
		return $people;
	}

	/*
	 * Static Functions
	 */
	public static function makeCompany()
	{
		
		if (defined('PRODUCTION') && PRODUCTION) 
		{
			
			$company = FALSE;
			
			if (MEMCACHED_ENABLED)
			{

				$cache		= Cache::Instance();
				$company	= $cache->get(array('company_blank', $tablename));
			
			}
			
			if (FALSE === $company)
			{
				
				$company = DataObjectFactory::Factory('Company');
				
				if (MEMCACHED_ENABLED)
				{

					$cache->add(
						array('company_blank', $tablename),
						serialize($company),
						28800
					);
				
				}
				
			}
			else
			{
				$company = unserialize($company);
			}	
			
			return $company;
			
		}
		
		$company = DataObjectFactory::Factory('Company');
		
		return $company;
		
	}

	/*
	 * Private Functions
	 */
	private function isValidAccountNumber($testaccnum)
	{
		$db = DB::instance();
		
		$query='SELECT COUNT(*) FROM company WHERE accountnumber = ' . $db->qstr($testaccnum) . ' AND usercompanyid = ' . $db->qstr(EGS_COMPANY_ID);
		
		$count = $db->GetOne($query);
		
		if ($count === "0")
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	function getSystemRelatedCompanies($_company_ids = array())
	{
		if (!empty($_company_ids))
		{
			$cc = new ConstraintChain();
			
			$cc->add(new Constraint($this->parent_field, 'in', '(' . implode(',', array_keys($_company_ids)) . ')'));
			
			$_company_ids += $this->getChildren($this->getAll($cc, TRUE));
			
			return $_company_ids;
		}
		
		return array();
	}
}


// End of Company
