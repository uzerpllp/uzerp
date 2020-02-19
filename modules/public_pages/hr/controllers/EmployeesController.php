<?php

/** 
 *	(c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 * 
 *	Released under GPLv3 license; see LICENSE.
 **/

class EmployeesController extends Controller
{

	protected $version='$Revision: 1.46 $';
	
	protected $_templateobject;
	
	private $_no_access_msg = 'Either the employee does not exist, or you do not have access to their details';
	
	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Employee');
		
		$this->uses($this->_templateobject);
		
	}

	public function index()
	{
		
		$errors = array();
		
		$s_data = array();
		
		// Set context from calling module
		$this->setSearch('employeeSearch', 'useDefault', $s_data);
		
		$this->_templateobject->authorisationPolicy();
		
		parent::index(new EmployeeCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['new'] = array(
					'tag' => 'New Employee',
					'link' => array('modules'	=> $this->_modules
								   ,'controller'=> $this->name
								   ,'action'	=> 'new')
		);
		
		$week_dates = $this->getWeekDates();
		
		$sidebarlist['hours'] = array(
					'tag'=>'Timesheet Hours',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> $this->name
								 ,'action'		=> 'view_hours_summary'
								 ,'start_date'	=> $week_dates['week_start_date']
								 ,'end_date'	=> $week_dates['week_end_date']
								 )
				 );
		
		$sidebarlist['payhistorys'] = array(
					'tag'=>'Pay History',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> 'employeepayhistorys'
								 ,'action'		=> 'index'
								 )
				 );
		
		$sidebarlist['payperiods'] = array(
					'tag'=>'Pay Periods',
					'link'=>array('modules'		=> $this->_modules
								 ,'controller'	=> 'employeepayperiods'
								 ,'action'		=> 'index'
								 )
				 );
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('no_delete',true);	
		$this->view->set('clickaction', 'view');
		
		
	}	

	public function delete()
	{
		sendBack();
	}
	
	public function leaver()
	{
		if (!$this->loadData())
		{
			$this->dataError($this->_no_access_msg);
			sendBack();
		}
		
		$this->view->set('employee', $this->_uses[$this->modeltype]);
		
	}
	
	public function save_leaver()
	{
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash = Flash::Instance();
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$employee = DataObject::Factory($this->_data[$this->modeltype], $errors, $this->modeltype);
		
		if (count($errors) == 0 && $employee && $employee->save())
		{
			$person = $employee->person;
			
			$person->end_date = $employee->finished_date;
			
			if (!$person->save())
			{
				$errors[] = 'Error updating person details '.$db->ErrorMsg();
			}
			
			$payrates = new EmployeeRateCollection(DataObjectFactory::Factory('EmployeeRate'));
			
			if ($payrates->close_off_current($employee->{$employee->idField}, $employee->finished_date)===FALSE)
			{
				$errors[] = 'Error closing off employee pay rates '.$db->ErrorMsg();
			}
			
		}
		
		if (count($errors) > 0)
		{
			$db->FailTrans();
			$db->CompleteTrans();
			$flash->addErrors($errors);
			$this->refresh();
		}
		
		$db->CompleteTrans();
		$flash->addMessage('Employee leaving date updated');
		
		sendTo($this->name, 'view', $this->_modules, array($employee->idField=>$employee->{$employee->idField}));
	}
	
	public function save_personal()
	{
	
		if (!$this->checkParams($this->modeltype) || !$this->loadData())
		{
			$this->dataError($this->_no_access_msg);
			sendBack();
		}
		
		$employee = $this->_uses[$this->modeltype];
		
		$flash = Flash::Instance();
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$party_id = $this->getPartyId($employee, $errors);
		
		// TODO: Need to move the saving of Party data to appropriate model
		// See also where saved in SordersController, CompanysController and PersonsController
		foreach (array('phone', 'mobile', 'email') as $contact_type)
		{
			if (!empty($this->_data[$this->modeltype][$contact_type]))
			{
				$cm = DataObjectFactory::Factory('PartyContactMethod');
				
				$cm_data = array();
				
				$cm_model	= 'Contactmethod';
				$pcm_model	= 'PartyContactMethod';
				
				$cm_data[$cm_model]['id']		= $this->_data[$this->modeltype]['contact_'.$contact_type.'_id'];
				$cm_data[$cm_model]['contact']	= $this->_data[$this->modeltype][$contact_type];
				
				$cm_data[$pcm_model]['id']					= '';
				$cm_data[$pcm_model]['contactmethod_id']	= $cm_data[$cm_model]['id'];
				$cm_data[$pcm_model]['party_id']			= $party_id;
				$cm_data[$pcm_model]['name']				= 'HOME';
				$cm_data[$pcm_model]['type']				= $cm->getType($contact_type);
				$cm_data[$pcm_model]['main']				= 'f';
				$cm_data[$pcm_model]['billing']				= 'f';
				$cm_data[$pcm_model]['shipping']			= 'f';
				$cm_data[$pcm_model]['payment']				= 'f';
				$cm_data[$pcm_model]['technical']			= 'f';
				
				$cm->loadBy(array('party_id', 'contactmethod_id', 'name', 'type')
						  , array($cm_data[$pcm_model]['party_id']
						  		, $cm_data[$pcm_model]['contactmethod_id']
						  		, $cm_data[$pcm_model]['name']
						  		, $cm_data[$pcm_model]['type']));
				
				if ($cm->isLoaded())
				{
					$cm_data[$pcm_model]['id'] = $cm->id;
				}
				
				$cm->check($cm_data);
				
				parent::save($pcm_model, $cm_data, $errors);
				
				$this->_data[$this->modeltype]['contact_'.$contact_type.'_id'] = $this->saved_model->contactmethod_id;
				
				$this->clearSavedModels($cm_model);
				$this->clearSavedModels($pcm_model);
			}
		}
		
		if (count($errors) == 0 && empty($this->_data[$this->modeltype]['address_id']))
		{
			$pa_model = 'PartyAddress';
			$pa = DataObjectFactory::Factory($pa_model);
			
			$pa_data['Address'] = $this->_data['Address'];
			
			$pa_data[$pa_model]['id']			= '';
			$pa_data[$pa_model]['address_id']	= '';
			$pa_data[$pa_model]['party_id']		= $party_id;
			$pa_data[$pa_model]['name']			= 'HOME';
			$pa_data[$pa_model]['main']			= 'f';
			$pa_data[$pa_model]['billing']		= 'f';
			$pa_data[$pa_model]['shipping']		= 'f';
			$pa_data[$pa_model]['payment']		= 'f';
			$pa_data[$pa_model]['technical']	= 'f';
			
			$pa->checkAddress($pa_data);
			
			$pa->loadBy(array('party_id', 'address_id', 'name')
					  , array($pa_data[$pa_model]['party_id']
						  	, $pa_data[$pa_model]['address_id']
						  	, $pa_data[$pa_model]['name']));
			
			if ($pa->isLoaded())
			{
				$pa_data[$pa_model]['id'] = $pa->id;
			}
			
			parent::save($pa_model, $pa_data, $errors);
			$this->_data[$this->modeltype]['address_id'] = $this->saved_model->address_id;
			
		}
		
		$ee_data[$this->modeltype] = $this->_data[$this->modeltype];
		
		if (count($errors) == 0 && parent::save($this->modeltype, $ee_data, $errors))
		{
			$db->CompleteTrans();
			
			$idfield = $this->_templateobject->idField;
			
			sendTo($this->name, 'view', $this->_modules, array($idfield => $employee->$idfield));
		}
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		$this->refresh();

	}
	
	public function save_work()
	{
	
		if (!$this->checkParams($this->modeltype) || !$this->loadData())
		{
			$this->dataError($this->_no_access_msg);
			sendBack();
		}
		
		$employee = $this->_uses[$this->modeltype];
		
		$flash = Flash::Instance();
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		$party_id = $this->getPartyId($employee, $errors);
		
		// TODO: Need to move the saving of Party data to appropriate model
		// See also where saved in SordersController, CompanysController and PersonsController
		$partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
		
		$cm_model	= 'Contactmethod';
		$pcm_model	= 'PartyContactMethod';
		
		foreach ($partycontactmethod->getEnumOptions('type') as $type=>$option)
		{
			$contact_data = $this->_data[$option];
			
			if (!empty($contact_data[$cm_model]['contact']))
			{
				if (empty($contact_data[$pcm_model]['type']))
				{
					$contact_data[$pcm_model]['type'] = $type;
				}
				
				if (empty($contact_data[$pcm_model]['party_id']))
				{
					$contact_data[$pcm_model]['party_id'] = $party_id;
				}
			
				parent::save($cm_model, $contact_data, $errors);
			}
			elseif (!empty($contact_data[$cm_model]['id']))
			{
				// Delete the entry
				$pcm = DataObjectFactory::Factory($pcm_model);
				$pcm->delete($contact_data[$pcm_model]['id'], $errors);
			}
		}
		
		$address_model	= 'Address';
		$party_model	= 'PartyAddress';
		
		if (empty($this->_data[$address_model]['fulladdress']))
		{
			$contact_data = array($address_model=>$this->_data[$address_model]
								 ,$party_model	=>$this->_data[$party_model]);
		}
		else
		{
			$contact_data = array($party_model	=>$this->_data[$party_model]);
		}
		
		$contact_data[$party_model]['address_id'] = $this->_data[$address_model]['fulladdress'];
		
		if (empty($contact_data[$party_model]['party_id']))
		{
			$contact_data[$party_model]['party_id'] = $party_id;
		}
		
		parent::save($address_model, $contact_data, $errors);
		
		if (count($errors) == 0)
		{
			$db->CompleteTrans();
			
			$idfield = $this->_templateobject->idField;
			
			sendTo($this->name, 'view', $this->_modules, array($idfield => $employee->$idfield));
		}
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		$this->refresh();

	}
	
	public function save()
	{
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash = Flash::Instance();
		$errors = array();
		
		$db = DB::Instance();
		$db->StartTrans();
		
		if (empty($this->_data[$this->modeltype]['person_id']))
		{
			$sc = DataObjectFactory::Factory('Systemcompany');
			
			$sc->load(EGS_COMPANY_ID);
			
			$data = array('title'		=> $this->_data[$this->modeltype]['title']
						 ,'firstname'	=> $this->_data[$this->modeltype]['firstname']
						 ,'middlename'	=> $this->_data[$this->modeltype]['middlename']
						 ,'surname'		=> $this->_data[$this->modeltype]['surname']
						 ,'suffix'		=> $this->_data[$this->modeltype]['suffix']
						 ,'company_id'	=> $sc->company_id
						 ,'jobtitle'	=> $this->_data[$this->modeltype]['jobtitle']
						 ,'department'	=> $this->_data[$this->modeltype]['department']
						 ,'reports_to'	=> $this->_data[$this->modeltype]['reports_to']);
			
			$person = DataObject::Factory($data, $errors, 'Person');
			
			if ($person && $person->save())
			{
				// Person does not exist, so create them
				$this->_data[$this->modeltype]['person_id'] = $person->id;
				
			}
			else
			{
				$errors[] = 'Error saving new employee details : '.$db->ErrorMsg();
			}
		}
		else
		{
			$person = DataObjectFactory::Factory('Person');
			
			$person->load($this->_data[$this->modeltype]['person_id']);
			
			if (empty($this->_data[$this->modeltype]['reports_to'])) { $this->_data[$this->modeltype]['reports_to']='NULL'; }
						
			if (!$person->isLoaded()
				 || !$person->update($person->id
				 					,array('title'
				 						  ,'firstname'
				 						  ,'middlename'
				 						  ,'surname'
				 						  ,'suffix'
				 						  ,'jobtitle'
				 						  ,'department'
				 						  ,'reports_to')
									,array($this->_data[$this->modeltype]['title']
										  ,$this->_data[$this->modeltype]['firstname']
										  ,$this->_data[$this->modeltype]['middlename']
										  ,$this->_data[$this->modeltype]['surname']
										  ,$this->_data[$this->modeltype]['suffix']
										  ,$this->_data[$this->modeltype]['jobtitle']
										  ,$this->_data[$this->modeltype]['department']
										  ,$this->_data[$this->modeltype]['reports_to'])
									))
			{
				$errors[] = 'Error updating employee details'.$db->ErrorMsg();
			}
		}
		
		if(count($errors) == 0 && isset($this->_data[$this->modeltype]['person_id']))
		{
			// Link them to any categories identified as Employee Categories
			$categories = DataObjectFactory::Factory('PeopleInCategories');
			
			// Get list of current categories assigned to this person
			$current_categories	= $categories->getCategoryID($person->id);
				
			$ledger_category = DataObjectFactory::Factory('LedgerCategory');
			
			// Get the people categories linked to HR
			$employee_categories = $ledger_category->getCategoriesByModel('employee');
			
			// Get list of people categories not currently assigned to this person 
			$insert_categories	= array_diff($employee_categories, $current_categories);
			
			// Not deleting any categories here - may need to add delete later (see PersonsController::save)
			if (count($insert_categories) > 0)
			{
				$categories->insert($employee_categories, $person->id, $errors);
			}
			
			$ee_data[$this->modeltype] = $this->_data[$this->modeltype];
			
			if (isset($this->_data['saveAnother']))
			{
				// Override default saveAnother called from parent::save
				$save_another = TRUE;
				unset($this->_data['saveAnother']);
			}
			
			if (parent::save($this->modeltype, $ee_data, $errors))
			{
				
				$this->saved_model->saveAuthorisation($this->_data[$this->modeltype]
													 ,'authorisation_type'
													 ,$errors
													 ,'HRAuthoriser');
				
				$this->saved_model->saveAuthorisers($this->_data[$this->modeltype]
												   ,'expense_authorisers_id'
												   ,$errors
												   ,'ExpenseAuthoriser');
				
				$this->saved_model->saveAuthorisers($this->_data[$this->modeltype]
												   ,'holiday_authorisers_id'
												   ,$errors
												   ,'HolidayAuthoriser');
			}
		}
		
		if (count($errors) == 0)
		{	
			$db->CompleteTrans();
			
			if ($save_another)
			{
				$this->saveAnother();
			}
			
			$idfield = $this->_templateobject->idField;
			
			sendTo($this->name, 'view', $this->_modules, array( $idfield => $this->saved_model->$idfield ));
		}
		
		$flash->addErrors($errors);
		
		$db->FailTrans();
		$db->CompleteTrans();
		
		$this->refresh();

	}
	
	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->_uses[$this->modeltype]->authorisationPolicy();
			
			if (!$this->loadData())
			{
				$this->dataError($this->_no_access_msg);
				sendBack();
			}
			
			// User can only view other peoples data
			$restrict_inserts = $readonly = TRUE;
		}
		
		$employee = $this->_uses[$this->modeltype];
		
		$idfield = $employee->idField;
		$idvalue = $employee->$idfield;
		
		// Do not allow the user to update their own details
		if ($idvalue == $employee->user_person_id)
		{
			$restrict_inserts = TRUE;
		}
		
		/* Get the details from the Person table*/
		$this->view->set('person', $employee->person);
		
		$this->view->set('holidays', $employee->getHolidayTotals());
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['view_all'] = array(
					'tag' => 'view_all',
					'link' => array('modules'	=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'	=>'index')
		);
		
		$sidebarlist['new_employee'] = array(
					'tag' => 'new_employee',
					'link' => array('modules'	=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'	=>'new')
		);
		
		$sidebar->addList('actions', $sidebarlist);

		$sidebarlist = array();
		
		$sidebarlist[$employee->employee] = array(
					'tag' => $employee->employee,
					'link' => array('modules'	=>$this->_modules
								   ,'controller'=>$this->name
								   ,'action'	=>'view'
								   ,$idfield	=>$idvalue)
		);
		
		if (!$readonly && is_null($employee->finished_date))
		{
			$sidebarlist['edit_employee'] = array(
						'tag' => 'Edit Employee Details',
						'link' => array('modules'	=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'	=>'edit'
									   ,$idfield	=>$idvalue)
			);
			
			$sidebarlist['edit_personal'] = array(
						'tag' => 'Edit Personal Details',
						'link' => array('modules'	=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'	=>'edit_personal'
									   ,$idfield	=>$idvalue)
			);
			
			$sidebarlist['edit_work'] = array(
						'tag' => 'Edit Work Contact Details',
						'link' => array('modules'	=>$this->_modules
									   ,'controller'=>$this->name
									   ,'action'	=>'edit_work'
									   ,$idfield	=>$idvalue)
			);
			
			$sidebarlist['make_leaver'] = array(
							'tag' => 'Leaver',
							'link' => array('modules'	=>$this->_modules
										   ,'controller'=>$this->name
										   ,'action'	=>'leaver'
										   ,$idfield	=>$idvalue)
			);

		}

		if (!is_null($employee->finished_date)) {
			$sidebarlist['delete_personal_data'] = [
				'tag' => 'Delete Personal Data',
				'link' => ['modules' => $this->_modules,
				   'controller' => $this->name,
				   'action' => 'deletePersonalData',
					$idfield => $idvalue],
				'id' => 'delete_personal_data'
			];
		}

		
		$sidebar->addList('currently_viewing', $sidebarlist);

		$sidebarlist = array();
		
		//restrict expenses to just viewing if leaver
		if (is_null($employee->finished_date))
		{
			$sidebarlist['expenses'] = array(
				'tag'=>'View Expenses',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'expenses'
							 ,'action'		=>'viewemployee'
							 ,'employee_id'	=>$idvalue),
				'new'=>array('modules'		=>$this->_modules
							,'controller'	=>'expenses'
							,'action'		=>'new'
							,'employee_id'	=>$idvalue)
			 );
	
			$sidebarlist['expenses_for_payment'] = array(
				'tag'=>'Expenses Awaiting Authorisation',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'expenses'
							 ,'action'		=>'viewemployee'
							 ,'employee_id'	=>$idvalue
							 ,'status'		=>Expense::statusAwaitingAuthorisation())
			 );
	
			$sidebarlist['make_payment'] = array(
				'tag'=>'Make Payment',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>$this->name
							 ,'action'		=>'make_payment'
							 ,'employee_id'	=>$idvalue)
			 );
	
			$sidebarlist['allocate_expenses'] = array(
				'tag'=>'Allocate Expenses to Payments',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>$this->name
							 ,'action'		=>'allocate'
							 ,'employee_id'	=>$idvalue)
			 );

		}
		else
		{
			$sidebarlist['expenses'] = array(
				'tag'=>'View Expenses',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'expenses'
							 ,'action'		=>'viewemployee'
							 ,'employee_id'	=>$idvalue)
			 );

		}

		$week_dates = $this->getWeekDates();
		
		if ($restrict_inserts || !is_null($employee->finished_date))
		{
			$sidebarlist['holidayentitlement'] = array(
					'tag'=>'Holiday Entitlement',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'holidayentitlements'
								 ,'action'		=>'viewemployee'
								 ,'employee_id'	=>$idvalue)
			);
			$sidebarlist['holidayrequest'] = array(
				'tag'=>'Holiday Request',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>$this->name
							 ,'action'		=>'viewholidayrequests'
							 ,'employee_id'	=>$employee->id)
			 );
	
			$sidebarlist['trainingplan'] = array(
				'tag'=>'Training Plan',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'employeetrainingplans'
							 ,'action'		=>'viewemployeetrainingplans'
							 ,'employee_id'	=>$idvalue)
			 );
		
			$sidebarlist['employeerates'] = array(
				'tag'=>'Pay Rates',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'employeerates'
							 ,'action'		=>'view_employee'
							 ,'employee_id'	=>$idvalue)
			);

			$sidebarlist['employeecontractdetails'] = array(
				'tag'=>'Contract Details',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'employeecontractdetails'
							 ,'action'		=>'viewemployee'
							 ,'employee_id'	=>$idvalue)
			);
			$sidebarlist['hours'] = array(
				'tag'=>'Timesheet Hours',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>$this->name
							 ,'action'		=>'view_hours_summary'
							 ,'person_id'	=>$employee->person_id
							 ,'start_date'	=>$week_dates['week_start_date']
							 ,'end_date'	=>$week_dates['week_end_date'])
			);
			
			$sidebarlist['payhistory'] = array(
					'tag'=>'Pay History',
					'link'=>array('modules'	=>$this->_modules
							,'controller'	=>'employeepayhistorys'
							,'action'		=>'view_employee'
							,'employee_id'	=>$idvalue)
			);
		}
		else
		{
			$sidebarlist['holidayentitlement'] = array(
					'tag'=>'Holiday Entitlement',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'holidayentitlements'
								 ,'action'		=>'viewemployee'
								 ,'employee_id'	=>$idvalue),
					'new'=>array('modules'		=>$this->_modules
								,'controller'	=>'holidayentitlements'
								,'action'		=>'new'
								,'employee_id'	=>$idvalue)
				 );
		
		
			$sidebarlist['holidayrequest'] = array(
					'tag'=>'Holiday Request',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>$this->name
								 ,'action'		=>'viewholidayrequests'
								 ,'employee_id'	=>$employee->id),
					'new'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'holidayrequests'
								 ,'action'		=>'new'
								 ,'employee_id'	=>$idvalue)
				 );
		
			$sidebarlist['trainingplan'] = array(
					'tag'=>'Training Plan',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'employeetrainingplans'
								 ,'action'		=>'viewemployeetrainingplans'
								 ,'employee_id'	=>$idvalue),
					'new'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'employeetrainingplans'
								 ,'action'		=>'new'
								 ,'employee_id'	=>$idvalue)
				 );
			$sidebarlist['employeerates'] = array(
					'tag'=>'Pay Rates',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'employeerates'
								 ,'action'		=>'view_employee'
								 ,'employee_id'	=>$idvalue),
					'new'=>array('modules'		=>$this->_modules
								,'controller'	=>'employeerates'
								,'action'		=>'new'
								,'employee_id'	=>$idvalue)
				 );
			$sidebarlist['employeecontractdetails'] = array(
					'tag'=>'Contract Details',
					'link'=>array('modules'		=>$this->_modules
								 ,'controller'	=>'employeecontractdetails'
								 ,'action'		=>'viewemployee'
								 ,'employee_id'	=>$idvalue),
					'new'=>array('modules'		=>$this->_modules
								,'controller'	=>'employeecontractdetails'
								,'action'		=>'new'
								,'employee_id'	=>$idvalue)
			);

			$sidebarlist['hours'] = array(
				'tag'=>'Timesheet Hours',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>$this->name
							 ,'action'		=>'view_hours_summary'
							 ,'person_id'	=>$employee->person_id
							 ,'start_date'	=>$week_dates['week_start_date']
							 ,'end_date'	=>$week_dates['week_end_date'])
			 );

			$sidebarlist['payhistory'] = array(
				'tag'=>'Pay History',
				'link'=>array('modules'		=>$this->_modules
							 ,'controller'	=>'employeepayhistorys'
							 ,'action'		=>'view_employee'
							 ,'employee_id'	=>$idvalue)
			 );
		
		}
		
		$sidebar->addList('related_items', $sidebarlist);
		
		$authoriser = DataObjectFactory::Factory('HRAuthoriser');
		
		foreach ($authoriser->getTypesForEmployee($idvalue) as $authorisation_type)
		{
			$array[] = $authoriser->getEnum('authorisation_type', $authorisation_type);
		}
		
		if (!empty($array))
		{
			$this->view->set('can_authorise', implode(',', $array));
		}
		
		$this->view->set('expense_authorisers', implode(',', $this->_templateobject->expense_model()->getAuthorisersByName($idvalue)));
		
		$this->view->set('holiday_authorisers', implode(',', $this->_templateobject->holiday_model()->getAuthorisersByName($idvalue)));
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function viewHolidayRequests()
	{
		$requests = new HolidayrequestCollection();
		
		$sh = $this->setSearchHandler($requests);
		
		$sh->addConstraint(new Constraint('employee_id','=',$this->_data['employee_id']));
		
		$requests->load($sh);
		
		$this->view->set('related_collection',$requests);
		
		$this->_templateName = $this->getTemplateName('view_related');
		
		$request->clickcontroller='holidayrequests';
		
		$this->view->set('clickaction','view');
		$this->view->set('clickcontroller','holidayrequests');
	}

	public function edit_personal()
	{
		if (!$this->checkParams('id') || !$this->loadData())
		{
			$this->dataError($this->_no_access_msg);
			sendBack();
		}
		
		$employee = $this->_uses[$this->modeltype];
		
		$this->view->set('employee', $employee);
		
		$this->view->set('person', $employee->person);
		
		$this->view->set('address', DataObjectFactory::Factory('address'));
		
		$addresses = $this->getAddresses($employee->person_id);
		$this->view->set('addresses', $addresses);
				
	}

	public function edit_work()
	{
		if (!$this->checkParams('id') || !$this->loadData())
		{
			$this->dataError($this->_no_access_msg);
			sendBack();
		}
		
		$employee = $this->_uses[$this->modeltype];
		
		$this->view->set('employee', $employee);
		
		$person = $employee->person;
		
		$this->view->set('person', $person);
		
		$partycontactmethod = DataObjectFactory::Factory('PartyContactMethod');
		
		$options = array();
		
		foreach ($partycontactmethod->getEnumOptions('type') as $option)
		{
			$options[$option] = $person->$option;
		}
		
		$this->view->set('options', $options);
		$this->view->set('main', 't');
		
		$this->view->set('address', DataObjectFactory::Factory('address'));
		
		$addresses = array(''=>'Select from list or enter new address below');
		$addresses += $this->getCompanyAddresses($person->company_id);
		$addresses += $this->getPersonAddresses($person->id);
		
		$this->view->set('addresses', $addresses);
		$this->view->set('partyaddress', $person->main_address);
		
	}

	public function edit() {
		$flash = Flash::Instance();

		if (!isset($this->_data) || !$this->loadData()) {
			// we are editing data, but either no id has been provided
			// or the data for the supplied id does not exist
			// or access to the record is denied
			$this->dataError($this->_no_access_msg);
			sendBack();
		}

		$employee = $this->_uses[$this->modeltype];
		if (!is_null($employee->finished_date)) {
			$flash->addError('Employee data cannot be edited, employee is a leaver');
			sendBack();
		}
		
		parent::edit();
		
	}
	
	public function _new()
	{

		parent::_new();
		
		$current_employee = $this->_uses[$this->modeltype];
		
		$cc = new ConstraintChain();
				
		$authoriser = DataObjectFactory::Factory('HRAuthoriser');
		$this->view->set('authorisation_types', $authoriser->getEnumOptions('authorisation_type'));
		
		$cc1 = new ConstraintChain();
		$cc2 = new ConstraintChain();
		
		if ($current_employee->isLoaded())
		{
			$this->view->set('person', $current_employee->person);
			
			$this->view->set('can_authorise', $authoriser->getTypesForEmployee($current_employee->{$current_employee->idField}));
			
			$this->view->set('expense_authorisers', $current_employee->getAuthorisers($current_employee->expense_model()));
			
			$this->view->set('holiday_authorisers', $current_employee->getAuthorisers($current_employee->holiday_model()));
			
			$cc1->add(new Constraint('employee_id', '<>', $current_employee->{$current_employee->idField}));
			$cc2->add(new Constraint('employee_id', '<>', $current_employee->{$current_employee->idField}));
		}
		
		$this->view->set('can_authorise_expenses', $authoriser->canAuthorise($cc1, $authoriser->expenses_type()));
		$this->view->set('can_authorise_holidays', $authoriser->canAuthorise($cc2, $authoriser->holidays_type()));
		
		// Get Current Employees by person_id=>name
		// Use person and list by hierarchy
		$person = DataObjectFactory::Factory('Person');
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company_id', '=', COMPANY_ID));
		$cc->add(new Constraint('id', 'IN ', '(select person_id from employees)'));
		$person->orderby = $person->identifierField = 'surname || \', \' || firstname';
		
		$reports_to = $person->getAll($cc);
		
		$this->view->set('reports_to', $reports_to);
		
		// Get list of People who are not currently Employees
		// and are either current users or are not users
		$current_people = array(''=>'Select from list or enter new person below');
		
		// Get list of people
		// 1) in current system company
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('company_id', '=', COMPANY_ID));
		
		// 2) who are not currently employees
		$cc->add(new Constraint('id', ' NOT IN ', '(select person_id from employees)'));
		
		// 3) who are still current
		$cc1 = new ConstraintChain();
		
		$cc1->add(new Constraint('end_date', 'is', 'NULL'));
		$cc1->add(new Constraint('end_date', '>', fix_date(date(DATE_FORMAT))), 'OR');
			
		$cc->add($cc1);
		
		$person->orderby = $person->identifierField = 'surname || \', \' || firstname';
		
		$current_people += $person->getAll($cc, TRUE);
		
		$this->view->set('people', $current_people);
		
		$this->view->set('address', DataObjectFactory::Factory('address'));
		
		$addresses = $this->getAddresses($current_employee->person_id);
		$this->view->set('addresses', $addresses);
	
	}

	public function allocate()
	{
		
		$employee = $this->_uses[$this->modeltype];
		
		$employee->load($this->_data['employee_id']);
		
		$transactions = $employee->getOutstandingTransactions(false);
		
		$this->view->set('transactions',$transactions);
		
		$this->view->set('no_ordering',true);
	}
	
	public function save_allocation()
	{
		$db = DB::Instance();
		
		$db->StartTrans();
		
		$total = 0;
		
		$base_total = 0;
		
		$flash = Flash::Instance();
		
		$errors = array();
		
		if(!isset($this->_data['transactions']))
		{
			$flash->addError('You must select at least one transaction');
		}
		else
		{
			$transactions = $this->_data['transactions'];
			
			foreach($transactions as $id=>$on)
			{
				$trans = DataObjectFactory::Factory('ELTransaction');
				
				$trans->load($id);
				
				$total = bcadd($total,$trans->os_value);
				
				$base_total = bcadd($base_total, $trans->base_os_value);
				
				$trans_store[] = $trans;
			}
			
			if ($total == 0)
			{
				foreach($trans_store as $transaction)
				{
					$transaction->status		= 'P';
					$transaction->os_value		= 0;
					$transaction->twin_os_value	= 0;
					$transaction->base_os_value	= 0;
					$transaction->for_payment	= 'f';
					
					if ($transaction->saveForPayment($errors) === false)
					{
						$flash->addErrors($errors);
						$flash->addError('Error saving transaction');
						$db->Failtrans();
						$db->CompleteTrans();
						sendback();				
					}
					
					if ($transaction->transaction_type == 'E')
					{
						$expense = DataObjectFactory::Factory('Expense');
						
						$expense->loadBy('expense_number', $transaction->our_reference);
						
						if (!$expense->isLoaded() || !$expense->update($expense->{$expense->idField}, 'status', $expense->statusPaid()))
						{
							$flash->addError('Error updating Expenses');
							$db->Failtrans();
							$db->CompleteTrans();
							sendback();				
						}
					}
				}
				
				if ($base_total!=0)
				{
					$data = array();
					
					$data['docref']	= $this->_data['employee_id'];
					$data['value']	= $base_total*-1;
					
					if (!ELTransaction::currencyAdjustment($data, $errors))
					{
						$flash->addErrors($errors);
						$db->FailTrans();
					}
				}
				
				if ($db->CompleteTrans())
				{
					$flash->addMessage('Transactions matched');
					sendBack();
				}
			}
			else
			{
				$flash->addError('Transactions must sum to zero');
			}
		}
		
		$db->FailTrans();
		
		$this->allocate();
		
		$this->setTemplatename('allocate');
	}

	public function make_payment()
	{

		$this->view->set('CBTransaction', DataObjectFactory::Factory('CBTransaction'));
		
		$this->view->set('company',SYSTEM_COMPANY);
		
		$this->view->set('company_id',COMPANY_ID);
		
		$employee = $this->_uses[$this->modeltype];
		
		if (isset($this->_data['employee_id']))
		{
			$employee->load($this->_data['employee_id']);
			$this->view->set('employees', array($employee->person_id=>$employee->employee));
		}
		else
		{
			$people = new EmployeeCollection();
			
			$sh = new SearchHandler($people, false);
			
			$sh->setFields(array('person_id', 'employee'));
			
			$people->load($sh);
			
			$this->view->set('employees', $people->getAssoc());
		}

		$tax = DataObjectFactory::Factory('TaxRate');
		$rates = $tax->getAll();
		$this->view->set('rates',$rates);
		
		$account = DataObjectFactory::Factory('CBAccount');
		
		$accounts = $account->getAll();
		
		$this->view->set('accounts', $accounts);
		
		if (isset($this->_data['cb_account_id']))
		{
			$default_account = $this->_data['cb_account_id'];
			
			$account->load($default_account);
			
			$this->view->set('account', $account->name);
		}
		else
		{
			$account->getDefaultAccount(key($accounts));
			
			$default_account = $account->{$account->idField};
		}
		
		$this->view->set('account_id', $default_account);
		
		$currency	= DataObjectFactory::Factory('Currency');
		$currencies	= $currency->getAll();
		
		$this->view->set('currencies',$currencies);
		
		$this->view->set('currency_id', $account->currency_id);
		$this->view->set('rate', $this->getAccountCurrencyRate($default_account, $account->currency_id));
		
	}
	
	public function savePayment()
	{
		$flash = Flash::Instance();
		
		$errors = array();
		
		if (!$this->checkParams('CBTransaction'))
		{
			sendBack();
		}
		
		$data = $this->_data['CBTransaction'];
		
		$data['source'] = 'E';
		
		$gl_params = DataObjectFactory::Factory('GLParams');
		
		$data['glaccount_id'] = $gl_params->expenses_control_account();
		
		$data['glcentre_id'] = $gl_params->balance_sheet_cost_centre();
		
		if (!isset($data['employee_id']))
		{
			$employee = $this->_uses[$this->modeltype];
			
			$employee->loadBy('person_id', $data['person_id']);
			
			$data['employee_id']=$employee->id;
		}
		
		$result = ELTransaction::saveTransaction($data,$errors);
		
		if ($result !== false)
		{
			$flash->addMessage('Expense Payment saved');
			
			if (isset($data['employee_id']) && !empty($data['employee_id']))
			{
				sendTo($this->name
					,'view'
					,$this->_modules
					,array('id'=>$data['employee_id']));
			}
			else
			{
				sendTo($this->name
					,'index'
					,$this->_modules);
			}
		}
		else
		{
			$flash->addErrors($errors);
			
			if (isset($data['employee_id']) && !empty($data['employee_id']))
			{
				$this->_data['employee_id'] = $data['employee_id'];
			}
			
			$this->refresh();
		}
	}

	public function process_hours()
	{
		
		$errors = array();
		
		$s_data = array();
		
		if (!empty($this->_data['person_id']))
		{
			$s_data['person_id'] = $this->_data['person_id'];
		}
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		else
		{
			$s_data['start_time']['from'] = date(DATE_FORMAT, strtotime("previous Monday"));
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		else
		{
			$s_data['start_time']['to'] = date(DATE_FORMAT, strtotime("next Monday")-1);
		}
		
		// Set context from calling module
		$this->setSearch('hoursSearch', 'person', $s_data);
		
		$date_range = $this->search->getValue('start_time');
		
		if (!empty($date_range['from']))
		{
			$this->view->set('start_date', fix_date($date_range['from']));
		}
		if (!empty($date_range['to']))
		{
			$this->view->set('end_date', fix_date($date_range['to']));
		}
		
		$hours = new HourCollection();
		
		$sh = $this->setSearchHandler($hours);
		
		$fields = array('type_id', 'person', 'person_id', 'type');
		
		if (!isset($this->_data['page']) && !isset($this->_data['orderby']))
		{
			$sh->setOrderBy($fields);
		}
		
		$sh->setGroupBy($fields);
		
		$this->_templateobject->identifierField = 'person_id';
		
		$this->_templateobject->authorisationPolicy();
		$employee_list = $this->_templateobject->getAll();
		
		if (empty($employee_list))
		{
			$employee_list = array(-1);
		}
		
		$sh->addConstraint(new Constraint('person_id', 'in', '(' . implode(',', $employee_list) . ')'));
		
		$fields[] = 'sum(duration) as total_hours';
		
		$sh->setFields($fields);
		
		parent::index($hours, $sh);
		
		$this->view->set('fields', array('person', 'type', 'total_hours'));
		$this->view->set('page_title', 'Hours Summary');
		$this->view->set('clickcontroller', 'hours');
		$this->view->set('clickaction', 'view');
		$this->view->set('linkfield', 'person_id');
		$this->view->set('linkvaluefield', 'name');
		
		$this->setTemplateName('hours_index');
		
	}
	
	public function view_hours_summary()
	{
		$errors = array();
		
		$s_data = array();
		
		if (!empty($this->_data['person_id']))
		{
			$s_data['person_id'] = $this->_data['person_id'];
		}
		
		if (isset($this->_data['start_date']))
		{
			$s_data['start_time']['from'] = empty($this->_data['start_date'])?'':un_fix_date($this->_data['start_date']);
		}
		else
		{
			$s_data['start_time']['from'] = date(DATE_FORMAT, strtotime("previous Monday"));
		}
		
		if (isset($this->_data['end_date']))
		{
			$s_data['start_time']['to'] = empty($this->_data['end_date'])?'':un_fix_date($this->_data['end_date']);
		}
		else
		{
			$s_data['start_time']['to'] = date(DATE_FORMAT, strtotime("next Monday")-1);
		}
		
		// Set context from calling module
		$this->setSearch('hoursSearch', 'person', $s_data);
		
		$date_range = $this->search->getValue('start_time');
		
		if (!empty($date_range['from']))
		{
			$this->view->set('start_date', fix_date($date_range['from']));
		}
		if (!empty($date_range['to']))
		{
			$this->view->set('end_date', fix_date($date_range['to']));
		}
		
		$hours = new HourCollection();
		
		$sh = $this->setSearchHandler($hours);
		
		$fields = array('type_id', 'person', 'person_id', 'type');
		
		if (!isset($this->_data['page']) && !isset($this->_data['orderby']))
		{
			$sh->setOrderBy($fields);
		}
		
		$sh->setGroupBy($fields);
		
		$this->_templateobject->identifierField = 'person_id';
		
		$this->_templateobject->authorisationPolicy();
		$employee_list = $this->_templateobject->getAll();
		
		if (empty($employee_list))
		{
			$employee_list = array(-1);
		}
		
		$sh->addConstraint(new Constraint('person_id', 'in', '(' . implode(',', $employee_list) . ')'));
		
		$fields[] = 'sum(duration) as total_hours';
		
		$sh->setFields($fields);
		
		parent::index($hours, $sh);
		
		$this->view->set('fields', array('person', 'type', 'total_hours'));
		$this->view->set('page_title', 'Hours Summary');
		$this->view->set('clickcontroller', 'hours');
		$this->view->set('clickaction', 'view');
		$this->view->set('linkfield', 'person_id');
		$this->view->set('linkvaluefield', 'name');
		
		$this->setTemplateName('hours_index');
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['new'] = array(
					'tag' => 'Enter Employee Hours',
					'link' => array('modules'	=> $this->_modules
								   ,'controller'=> 'hours'
								   ,'action'	=> 'new'
								   ,'company_id'=> COMPANY_ID)
		);
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
	}

	/**
	 * Remove employee leaver personal data and contact details
	 *
	 * Removes fields defined on the Employee Model as containing
	 * personal data and any phone numbers, email addresses,
	 * postal addresses, etc. found attached to the employees
	 * Person/Party.
	 *
	 * @return void
	 */
	public function deletePersonalData() {
		$this->checkRequest(['post']);

		$flash = Flash::Instance();
		$errors = [];

		if (count($this->_data['employee']) == 0) {
			$flash->addMessage('No data types were selected for deletion');
			sendBack();
		}

		$employee = $this->_uses[$this->modeltype];
		$employee->load($this->_data['id']);
		if(!$employee->isLoaded()) {
			$flash->addError('Employee record could not be loaded');
			sendBack();
		}

		if (!$this->loadData()) {
			$this->_uses[$this->modeltype]->authorisationPolicy();
			if (!$this->loadData()) {
				$this->dataError($this->_no_access_msg);
				sendBack();
			}
		}

		if (is_null($employee->finished_date)) {
			$flash->addError('Personal data cannot be deleted, employee is not a leaver');
			sendBack();
		}

		foreach($employee->getPersonalDataFields() as $fieldname => $params) {
			if(array_key_exists($fieldname, $this->_data['employee'])) {
				$employee->$fieldname = $params['value'];
			}
		}

		$party = new Party();
		$person = new Person();
		$addresses = new PartyAddressCollection();

		$person->load($employee->person_id);
		$methods = $person->getContactMethods();
		if (count($methods) > 0){
			$party->load($person->party_id);

			$address_sh = new SearchHandler($addresses, false);
			$cc = new ConstraintChain();
			$cc->add(new Constraint('party_id', '=', $party->id));
			$address_sh->addConstraintChain($cc);
			$addresses->load($address_sh);
		}

		$db = DB::Instance();
		$db->StartTrans();

		if ($this->_data['employee']['contact_methods'] === 'on' && count($methods) > 0) {
			foreach($methods as $method) {
				$partymethod = new PartyContactMethod();
				$partymethod->delete($method->id, $errors);
			};
			$employee->contact_phone_id = '';
			$employee->contact_mobile_id = '';
			$employee->contact_email_id = '';
		}

		if ($this->_data['employee']['addresses'] === 'on' && count($addresses) > 0) {
			foreach($addresses as $address) {
				$partyaddress = new PartyAddress();
				$partyaddress->delete($address->id, $errors);
			};
			$employee->address_id = '';
		}

		if(count($errors) > 0) {
			$db->FailTrans();
			$flash->addErrors($errors);
			sendBack();
		}

		if(!$employee->save()) {
			$db->FailTrans();
			$flash->addError('Failed to delete employee personal data');
			sendBack();
		}

		$db->CompleteTrans();
		$flash->addMessage('Employee personal data deleted successfully');
		sendBack();
	}

	/*
	 * Ajax Functions
	 */
	function getAddresses($_person_id = '')
	{
	
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['person_id'])) {
				$_person_id=$this->_data['person_id'];
			}
			if(!empty($this->_data['fulladdress'])) {
				$_fulladdress=$this->_data['fulladdress'];
			}
		}
	
		$address = DataObjectFactory::Factory('personaddress');
		
		$cc = new ConstraintChain();
		$cc->add(new Constraint('person_id', '=', $_person_id));
		
		$addresses = $address->getAll($cc, true, true);
	
		$addresses = array(''=>'Select existing or enter new address')+$addresses;
		
		if(isset($this->_data['ajax']))
		{
			if (!empty($_fulladdress))
			{
				$this->view->set('value',$_fulladdress);
			}
			$this->view->set('options',$addresses);
			$this->setTemplateName('select_options');
		}
		else
		{
			return $addresses;
		}
	
	}
	
	public function getCurrencyRate ($_cb_account_id = '', $_currency_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['cb_account_id'])) { $_cb_account_id = $this->_data['cb_account_id']; }
			if(!empty($this->_data['currency_id'])) { $_currency_id = $this->_data['currency_id']; }
		}
		
		$rate = $this->getAccountCurrencyRate($_cb_account_id, $_currency_id);
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value', $rate);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $rate;
		}
		
	}
	
	function getPersonData($_person_id = '')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['person_id'])) {
				$_person_id=$this->_data['person_id'];
			}
		}
		
		$person = DataObjectFactory::Factory('Person');
		$person->load($_person_id);
		
		$output['title']		= array('data'=>$person->title,'is_array'=>FALSE);
		$output['firstname']	= array('data'=>$person->firstname,'is_array'=>FALSE);
		$output['middlename']	= array('data'=>$person->middlename,'is_array'=>FALSE);
		$output['surname']		= array('data'=>$person->surname,'is_array'=>FALSE);
		$output['suffix']		= array('data'=>$person->suffix,'is_array'=>FALSE);
		$output['jobtitle']		= array('data'=>$person->jobtitle,'is_array'=>FALSE);
		$output['department']	= array('data'=>$person->department,'is_array'=>FALSE);
		$output['reports_to']	= array('data'=>$person->reports_to,'is_array'=>FALSE);
				
		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		if(isset($this->_data['ajax'])) {
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		} else {
			return $output;
		}
	
	}
	
	/*
	 * Private functions
	 */
	private function getAccountCurrencyRate($_cb_account_id = '', $_currency_id = '')
	{
		$rate = '';
		
		$glparams = DataObjectFactory::Factory('GLParams');
		
		if (!empty($_currency_id) && $_currency_id != $glparams->base_currency())
		{
			$currency = DataObjectFactory::Factory('Currency');
			$currency->load($_currency_id);
			$rate = $currency->rate;
		}
		
		if (empty($rate) && !empty($_cb_account_id))
		{
			$cbaccount = DataObjectFactory::Factory('CBAccount');
			$cbaccount->load($_cb_account_id);
			if ($cbaccount->currency_id != $glparams->base_currency())
			{
				$rate = $cbaccount->currency_detail->rate;
			}
		}
		
		return $rate;
	}
	
	private function getWeekDates()
	{
		$hr_params = DataObjectFactory::Factory('HRParameters');
		
		return $hr_params->get_week_dates($errors);
		
	}
	
	private function getCompanyAddresses($_company_id = '')
	{
		$addresses = DataObjectFactory::Factory('companyaddress');
		
		return $addresses->getAddresses($_company_id);
		
	}
	
	private function getPersonAddresses($_person_id = '')
	{
		$addresses = DataObjectFactory::Factory('personaddress');
		
		return $addresses->getAddresses($_person_id);
		
	}
	
	private function getPartyId($employee, &$errors = array())
	{
		
		$person = $employee->person;
		
		if (is_null($person->party_id))
		{
			$party_data = array('type'=>'Person');
			
			parent::save('party', $party_data, $errors);
			
			$person->party_id = $this->saved_model->id;
			
			$person->save();
		}
		
		return $person->party_id;
		
	}
	
}

// End of EmployeesController
