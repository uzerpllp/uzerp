<?php

/** 
 *	(c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 * 
 *	Released under GPLv3 license; see LICENSE.
 **/

class Employee extends DataObject {
	
	protected $version = '$Revision: 1.27 $';
	
	protected $defaultDisplayFields = array(
		'employee_number'	=> 'employee_number',
		'employee'			=> 'employee',
		'gender'			=> 'gender',
		'works_number'		=> 'works_number',
		'pay_basis'			=> 'pay_basis',
		'employee_grade'	=> 'employee_grade',
		'department'		=> 'department',
		'ni'				=> 'NI Number',
		'start_date'		=> 'start_date',
		'finished_date'		=> 'finished_date'
	);

	/**
	 * Identify field names on this model that may contain personal data
	 *
	 * @var array  Associative array
	 *  [<DB field name> => [
	 *    'label' => <Text on forms, messages, etc.>,
	 *    'value' => <Value that should overwrite stored values>]]
	 */
	protected $personal_data_fields = [
		'ni' => [
			'label' => 'NI Number',
			'value' => ''],
		'dob' => [
			'label' => 'Date of Birth',
			'value' => ''],
		'gender' => [
			'label' => 'Gender',
			'value' => 'O'],
		'next_of_kin' => [
			'label' => 'Next of Kin Name',
			'value' => ''],
		'nok_address' => [
			'label' => 'Next of Kin Address',
			'value' => ''],
		'nok_phone' => [
			'label' => 'Next of Kin Phone',
			'value' => ''],
		'nok_relationship' => [
			'label' => 'Next of Kin Relationship',
			'value' => ''],
		'bank_name' => [
			'label' => 'Bank Name',
			'value' => ''],
		'bank_address' => [
			'label' => 'Bank Address',
			'value' => ''],
		'bank_account_name' => [
			'label' => 'Bank Account Name',
			'value' => ''],
		'bank_account_number' => [
			'label' => 'Bank Account Number',
			'value' =>''],
		'bank_sort_code' => [
			'label' =>'Bank Sort Code',
			'value' => '']
	];
	
	protected $user_person_id;
	
	function __construct($tablename = 'employees')
	{
		
		// Register non-persistent attributes
		
		// Contruct the object
		parent::__construct($tablename);
		
		// Set specific characteristics
		$this->idField			= 'id';
		$this->identifierField	= 'employee';
		
		$user = getCurrentUser();
		$this->user_person_id = $user->person_id;
		
		$this->orderby = 'employee_number';
		
		// Define relationships
		$this->belongsTo('Person', 'person_id', 'employee', '', "surname ||' '|| firstname");
		$this->belongsTo('Company', 'company_id', 'company');
		$this->belongsTo('EmployeeGrade', 'employee_grade_id', 'employee_grade');
		$this->belongsTo('Address', 'address_id', 'address');
		$this->belongsTo('ContactMethod', 'contact_phone_id', 'phone');
		$this->belongsTo('ContactMethod', 'contact_mobile_id', 'mobile');
		$this->belongsTo('ContactMethod', 'contact_email_id', 'email');
		$this->belongsTo('MFDept', 'mfdept_id', 'department');
		$this->hasOne('Person', 'person_id', 'person');
		$this->hasOne('Address', 'address_id', 'personal_address');
		$this->hasMany('EmployeeRate', 'employee_rates', 'employee_id');
		$this->hasMany('ExpenseAuthoriser', 'expense_authorisers', 'employee_id');
		$this->hasMany('HolidayAuthoriser', 'holiday_authorisers', 'employee_id');
		$this->hasMany('Hour', 'hours', 'person_id', 'person_id');
		$this->hasMany('EmployeePayHistory', 'pay_history', 'employee_id');
		$this->hasMany('HRAuthoriser', 'can_authorise', 'employee_id');
		
		// Define default values
		$params = DataObjectFactory::Factory('GLParams');
		$base_currency = $params->base_currency();

		// Define field formats
		$this->getField('expenses_balance')->setFormatter(new CurrencyFormatter($base_currency));

		// Define validation
		$this->validateUniquenessOf('employee_number');
		$this->validateUniquenessOf('works_number', NULL, TRUE);
		$this->validateUniquenessOf(array('ni', 'finished_date'), NULL, TRUE);
		
		// Define enumerated types
        $this->setEnum('gender', array(
            'M' => 'Male',
            'F' => 'Female',
            'T' => 'Transgender',
            'G' => 'Gender non-conforming',
            'O' => 'Other',
			'D' => 'Declined to answer'
		));
		
		$this->setEnum(
			'pay_basis',
			array(
					'M'	=> 'Monthly',
					'W'	=> 'Weekly',
			)
		);
		
		// Define link rules for related items
	
	}
	
	function expense_model()
	{
		return DataObjectFactory::Factory('ExpenseAuthoriser');
	}
	
	function holiday_model()
	{
		return DataObjectFactory::Factory('HolidayAuthoriser');
	}
	
	function getAuthorisees($authoriser)
	{
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('authoriser_id', '=', $this->{$this->idField}));
		
		$authoriser->identifierField = 'employee_id';
		
		return $authoriser->getAll($cc);
		
	}

	function getAuthorisers($authoriser)
	{
		
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $this->{$this->idField}));
		
		$authoriser->identifierField = 'authoriser_id';
		
		return $authoriser->getAll($cc);
		
	}

	function deleteAuthorisers($do)
	{
		$db=DB::Instance();
		
		$db->StartTrans();
		
		foreach ($this->getHasmany() as $name=>$hasMany)
		{
			if ($hasMany['do'] == $do)
			{
				foreach ($this->$name as $authoriser)
				{
					if (!$authoriser->delete($authoriser->id))
					{
						$db->FailTrans();
						break;
					}
				}
			}
		}
		
		return $db->CompleteTrans();
	}

	function saveAuthorisers($data, $field, &$errors, $do)
	{
		$db=DB::Instance();
		
		$db->StartTrans();

		// Delete any existing entries
		$this->deleteAuthorisers($do);
		
		// Insert new entries if any
		if (!empty($data[$field]))
		{
			foreach ($data[$field] as $authoriser_id)
			{
				$authoriser = DataObject::Factory(array('employee_id'=>$this->id
													   ,'authoriser_id'=>$authoriser_id)
												 ,$errors
												 ,$do);
											   
				if (!$authoriser || count($errors)>0 || !$authoriser->save())
				{
					$errors[] = 'Error saving '.$do.' : '.$db->ErrorMsg();
					$db->FailTrans();
					break;
				}
			}
		}
		
		return $db->CompleteTrans();
	}
	
	function saveAuthorisation($data, $field, &$errors, $do)
	{
		$db=DB::Instance();
		
		$db->StartTrans();
		
		// Delete any existing entries
		$this->deleteAuthorisers($do);
		
		// Insert new entries if any
		if (!empty($data[$field]))
		{
			foreach ($data[$field] as $authorisation_type)
			{
				$authoriser = DataObject::Factory(array('employee_id'=>$this->id
													   ,'authorisation_type'=>$authorisation_type)
												 ,$errors
												 ,$do);
											   
				if (!$authoriser || count($errors)>0 || !$authoriser->save())
				{
					$errors[] = 'Error saving '.$do.' : '.$db->ErrorMsg();
					$db->FailTrans();
					break;
				}
			}
		}
		
		return $db->CompleteTrans();
	}
	
	function isAuthorised($authoriser)
	{
		// Identifies if the current user is authorised to act
		// for another employee - e.g. to input their holidays
		$user = getCurrentUser();
		
		if ($user && !is_null($user->person_id))
		{
			if ($this->person_id == $user->person_id)
			{
				// The user is the employee
				return true;
			}
			
			if ($this->person->reports_to == $user->person_id)
			{
				// The user is the employees manager
				return true;
			}
			
			$employee = DataObjectFactory::Factory('Employee');
			
			$employee->loadBy('person_id', $user->person_id);
			
			// The user is an authoriser for the employee
			return $authoriser->isAuthorised($this->id, $employee->id);
			
		}
		
		return false;
		
	}
	
	function authorisationPolicy($authoriser = '')
	{
		$cc = new ConstraintChain();
		
		if (empty($this->user_person_id))
		{
			return;
		}
		
		// The user is the employee
		$cc->add(new Constraint('person_id', '=', $this->user_person_id), 'OR');
		
		// The user is the employees manager
		$cc->add(new Constraint($this->user_person_id, '=', '(select reports_to from person where id = person_id)'), 'OR');
		
		$employee = DataObjectFactory::Factory('employee');
		
		if (SYSTEM_POLICIES_ENABLED) {
			$employee->_policyConstraint['constraint']->add(new Constraint('person_id', '=', $this->user_person_id), 'OR');
			$employee->_policyConstraint['name'][] = 'Own detail';
			$employee->_policyConstraint['field'][] = 'person_id';
		}
		
		$employee->loadBy('person_id', $this->user_person_id);
		
		$authorisation_list = array();
		 
		if (empty($authoriser))
		{
			$authorisation_list = $employee->getAuthorisees($this->holiday_model());
			$authorisation_list += $employee->getAuthorisees($this->expense_model());
		}
		else
		{
			$authorisation_list = $employee->getAuthorisees($authoriser);
		}
		
		if (count($authorisation_list) > 0)
		{
			// The user is an authoriser for the employee
			$cc->add(new Constraint($this->idField, 'in', '('.implode(',', $authorisation_list).')'), 'OR');
		}
		
		if (SYSTEM_POLICIES_ENABLED) {
			$this->_policyConstraint['constraint']->add($cc, 'OR');
			$this->_policyConstraint['name'][] = 'Is Authorised';
			$this->_policyConstraint['field'][] = 'id';
		}
		
		return $cc;
		
	}
	
	public function getAll(ConstraintChain $cc=null, $ignore_tree=false, $use_collection=false, $limit='')
	{
		return parent::getAll($cc, $ignore_tree, true, $limit);
	}

	public function getOutstandingTransactions($extract=true, $cc='')
	{
		$transactions = new ELTransactionCollection();
		
		$sh = new SearchHandler($transactions,false);
		
		if($extract)
		{
			$sh->extract();
		}
		
		$sh->addConstraint(new Constraint('status','=','O'));
		
		if($this->id)
		{
			$sh->addConstraint(new Constraint('employee_id','=',$this->id));
		}
		
		if (!empty($cc) && $cc instanceOf ConstraintChain)
		{
			$sh->addConstraintChain($cc);
		}
		
		$sh->setOrderby(array('employee', 'our_reference'));
		
		$transactions->load($sh);
		
		return $transactions;
	}
	
	public function getOutstandingHolidays()
	{
		
		$holidayEntitlement = DataObjectFactory::Factory('Holidayentitlement');
		
		$date = fix_date(date(DATE_FORMAT));
		
		return $holidayEntitlement->get_total_days_left($date, $this->{$this->idField});
		
	}
	
	public function getHolidayTotals()
	{
		
		$holidayEntitlement = DataObjectFactory::Factory('Holidayentitlement');
		
		$date = fix_date(date(DATE_FORMAT));
		
		return $holidayEntitlement->get_totals($date, $this->{$this->idField});
		
	}
	
	public function updateBalance(ELTransaction $eltrans)
	{
		$cc = new ConstraintChain();
		
		$id = $this->{$this->idField};
		
		$cc->add(new Constraint('employee_id', '=', $id));
		
		$amount = $eltrans->outstandingBalance($cc);
		
		if ($this->update($id, 'expenses_balance', $amount))
		{
			return true;
		}
		
		return false;
		
	}

	public function getPersonalDataFields () {
		return $this->personal_data_fields;
	}

	/*
	 * Public Static Functions
	 */
	public static function Factory($data, &$errors = array(), $do_name = 'Employee')
	{
		
		$unique_field = 'employee_number';
		
		$do = DataObjectFactory::Factory($do_name);
		
		if (empty($data[$do->idField]) && empty($data[$unique_field]))
		{
			
			$do->identifierField = $unique_field;
			
			$generator	= new UniqueNumberHandler();
			
			$data[$unique_field] = $generator->handle($do);
			
		}
		
		return parent::Factory($data, $errors, $do);
		
	}
	
}

// End of Employee