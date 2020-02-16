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
		$this->belongsTo('CompanyClassification','classification_id', 'company_classification');
		$this->belongsTo('CompanyIndustry', 'industry_id', 'company_industry');
		$this->belongsTo('CompanyRating', 'rating_id', 'company_rating');
		$this->belongsTo('CompanySource', 'source_id', 'company_source');
		$this->belongsTo('CompanyStatus', 'status_id', 'company_status');
		$this->belongsTo('CompanyType', 'type_id', 'company_type');

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

	/**
	 * Make company contact and associated people inactive
	 *
	 * @param Date $date
	 * @param string $ledger_type
	 * @throws Exception code 0 = Informational, code 1 = Error
	 * @return bool true = success, false = failure
	 */
	public function makeInactive($date = null, $ledger_type = null)
	{
		$result = false;

		$customer = new SLCustomer();
		$supplier = new PLSupplier();
		$has_customer = $customer->loadBy('company_id', $this->{$this->idField});
		$has_supplier = $supplier->loadBy('company_id', $this->{$this->idField});

		// When called while making a Sales/Purchase Ledger entry inactive, this method
		// should have a ledger type set, PL or SL. If an active entry exists
		// then don't make the contact details inactive.
		if ($ledger_type !== null) {
			$category = DataObjectFactory::Factory('LedgerCategory');
			$categories = $category->checkCompanyUsage($this->company_id);
			$categories = array_filter($categories,
				function ($key) {
					return in_array($key, ['PL', 'SL']);
				}, ARRAY_FILTER_USE_KEY
			);
		
			if (count($categories) > 1) {
				if ($ledger_type == 'SL' && $has_supplier && $supplier->date_inactive == null) {
					throw (new Exception('Contact details not ended as Purchase Ledger Supplier in use.'));
				}
				if ($ledger_type == 'PL' && $has_customer && $customer->date_inactive == null) {
					throw (new Exception('Contact details not ended as Sales Ledger Customer in use.'));
				}
			}
		}

		// Don't save the inactive date on a company contact that is linked
		// to an active Sales/Purchase Ledger entry.
		if (($ledger_type === null && $has_supplier && $supplier->date_inactive == null && $this->date_inactive !== "null") || ($ledger_type === null && $has_customer && $customer->date_inactive == null && $this->date_inactive !== "null")){
			$this->date_inactive = '';
			$this->save();
			throw (new Exception('Inactive date not set. Contact details linked to an active Sales/Purchase Ledger Customer.', 1));
		}

		// Set the inactive date
		if ($date !== null) {
			$this->date_inactive = $date;
		}
		$result = $this->save();

		// Set the inactive date on associated people
		if ($this->date_inactive !== 'null' && !$this->isSystemCompany() && $result) {
			// Make a collection using the table. The default is to use the
			// database view and we can't update via that.
			$people = new PersonCollection('Person', 'person');
			$sh = new SearchHandler($people, false);
			$sh->addConstraint(new Constraint('company_id', '=', $this->{$this->idField}));
			$sh->addConstraint(new Constraint('end_date', 'IS', 'NULL'));
			$people->load($sh);
			$row_count = $people->update('end_date', $date, $sh);
			if ($row_count !== false) {
				$result = true;
			} else {
				$result = false;
			}
			return $result;
		}

		return $result;
	}

	/**
	 * Make company contact active
	 *
	 * @return bool
	 */
	public function makeActive()
	{
		$result = false;
		$this->date_inactive = '';
		$result = $this->save();
		return $result;
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
			
			$_company_ids += $this->getAll($cc, TRUE);
			
			return $_company_ids;
		}
		
		return array();
	}

	/**
	 * Check if this Company instance is
	 * connected to a system company
	 *
	 * @return boolean
	 */
	function isSystemCompany()
	{
		$system_company = new Systemcompany();
		$system_company->loadBy('company_id', $this->{$this->idField});
		if ($system_company->isLoaded()) {
			return true;
		}
		return false;
	}
}


// End of Company
