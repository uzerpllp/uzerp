<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HolidayrequestsController extends HrController
{

	protected $version='$Revision: 1.18 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('Holidayrequest');
		
		$this->uses($this->_templateobject);
		$this->view->set('controller', 'Holidayrequest');

	}

	public function index()
	{
		$status_enums=$this->_templateobject->getEnumOptions('status');
		$this->view->set('status_enums',$status_enums);
			$legend=array($status_enums['A']=>'fc_green',
			$status_enums['C']=>'fc_grey',
			$status_enums['D']=>'fc_red',
			$status_enums['W']=>'fc_yellow'
		);
		$this->view->set('legend',$legend);

		$hol_sidebar = [];
		$hol_sidebar['new_request'] = [
			'link' => [
				'modules'=>$this->_modules,
				'controller'=>$this->name,
				'action'=>'_new'
			],
			'tag'=>'New Request'
		];
		$hol_sidebar['list_view'] = [
			'link' => [
				'modules'=>$this->_modules,
				'controller'=>$this->name,
				'action'=>'holidayrequestslist'
			],
			'tag'=>'View List'
		];
		$sidebar = new SidebarController($this->view);
		$sidebar->addList('Actions', $hol_sidebar);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function holidayRequestsList()
	{	
		$s_data = array();
		$this->setSearch('holidaySearch', 'useDefault', $s_data);
		$this->view->set('clickaction', 'view');
		$collection = new HolidayrequestCollection($this->_templateobject);
		$collection->orderby = ['start_date'];
		$collection->direction = ['DESC'];
		parent::index($collection);
		
		$hol_sidebar = [];
		$hol_sidebar['new_request'] = [
			'link' => [
				'modules'=>$this->_modules,
				'controller'=>$this->name,
				'action'=>'_new'
			],
			'tag'=>'New Request'
		];
		$hol_sidebar['cal_view'] = [
			'link' => [
				'modules'=>$this->_modules,
				'controller'=>$this->name,
				'action'=>'index'
			],
			'tag'=>'View Calendar'
		];
		$sidebar = new SidebarController($this->view);
		$sidebar->addList('Actions', $hol_sidebar);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function _new()
	{
		$flash = Flash::Instance();
		
		parent::_new();
		
		$holidayRequest = $this->_uses[$this->modeltype];
		
		// Check if authorisation is allowed
		$employee = DataObjectFactory::Factory('Employee');
		$employee->orderby = 'employee';
		
		$employee->authorisationPolicy($employee->holiday_model());
		
		if ($holidayRequest->isLoaded())
		{
			
			if (!$holidayRequest->awaitingAuthorisation())
			{
				$flash->addError('This request cannot be amended because it has been '.$holidayRequest->getFormatted('status'));
				sendBack();
			}
			
			$employee_id = $holidayRequest->employee_id;
		}
		else
		{
			if (!empty($this->_data['employee_id']))
			{
				$employee_id = $this->_data['employee_id'];
				$employee->load($this->_data['employee_id']);
				$this->view->set('title', ' for ' . $employee->person->getIdentifierValue());
			}
			else
			{
				$employee_id = $this->get_employee_id();
			}


			if (!empty($this->_data['start_date']))
			{
				$holidayRequest->start_date = fix_date($this->_data['start_date']);
			}
			
			if (!empty($this->_data['end_date']))
			{
				$holidayRequest->end_date = fix_date($this->_data['end_date']);
			}
			
			if (!empty($this->_data['start_date']) && !empty($this->_data['end_date']))
			{
				unset($this->_data['ajax']);
				$holidayRequest->num_days = $this->getNumberDays($employee_id, $this->_data['start_date'], $this->_data['end_date']);
			}
			
		}
		if (empty($employee_id))
		{
			$flash->addError('You need to select an employee');
			sendBack();
		}
		
		$employee->load($employee_id);

		if (!$employee->isLoaded())
		{
			$flash->addError('Error loading employee details');
			sendBack();
		}
		
		if (!is_null($employee->finished_date) && $employee->finished_date < fix_date(date(DATE_FORMAT)))
		{
			$flash->addError('Employee has left');
			sendBack();
		}
		
		if (!$employee->isLoaded())
		{
			$flash->addError('You cannot set up a holiday request for this person');
			sendBack();
		}
		
		$days_left = $this->getDaysLeft($employee_id);
		
		if ($holidayRequest->isLoaded() && $holidayRequest->special_circumstances=='f')
		{
			// Editing request that is not for Special Circumstances
			$this->view->set('new_days_left', $days_left);
			$this->view->set('days_left', bcadd($days_left, $holidayRequest->num_days, 1));
		}
		else
		{
			// New request, or editing existing request for Special Circumstances
			$this->view->set('days_left', $days_left);
			$this->view->set('new_days_left', bcsub($days_left, $holidayRequest->num_days, 1));
		}
		
		$this->view->set('employee', $employee);
		// requests can only be for current employees
		$cc = new ConstraintChain();
		$cc->add(new Constraint('finished_date', 'is', 'NULL'));
		$this->view->set('employees', $employee->getAll($cc, TRUE, TRUE));
		$this->view->set('today', date(DB_DATE_FORMAT));
	}
	
	public function view_my_holiday_requests()
	{
		$flash = Flash::Instance();
		$employee_id = $this->get_employee_id();
		
		if (!empty($employee_id))
		{
			$this->_templateName = $this->getTemplateName('index');
			
			$hr = new HolidayrequestCollection($this->_templateobject);
			
			$sh = $this->setSearchHandler($hr);
			
			$sh->addConstraint(new Constraint('employee_id', '=', $employee_id));
			
			parent::index($hr, $sh);
			
			//$this->view->set('clickaction','view');
			$status_enums=$this->_templateobject->getEnumOptions('status');
		
			$this->view->set('status_enums',$status_enums);
				$legend=array($status_enums['A']=>'fc_green',
				$status_enums['C']=>'fc_grey',
				$status_enums['D']=>'fc_red',
				$status_enums['W']=>'fc_yellow'
			);
			$this->view->set('legend',$legend);
			$sidebar = new SidebarController($this->view);
			$this->view->register('sidebar',$sidebar);
			$this->view->set('sidebar',$sidebar);
			$this->view->set('employee_id', $employee_id);
		}
		else
		{
			$flash->addError('You have not been set up as an employee');
			sendBack();
		}
		
	}		

	public function delete()
	{
		$flash = Flash::Instance();
		
//		parent::delete('Holidayrequest');
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
				
		$holidayRequest = $this->_uses[$this->modeltype];
				
		$holidayRequest->status = $holidayRequest->cancel();
		
		if ($holidayRequest->save())
		{
			sendTo('employees'
					,'view'
					,$this->_modules
					,array('id' => $holidayRequest->employee_id));
		}
		
		$db = DB::Instance();
		
		$flash->addError('Error cancelling holiday request : '.$db->ErrorMsg());
		$this->refresh();
		
	}
	
	public function save()
	{
		$flash=Flash::Instance();
		
		$errors=array();
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		/* Get the Holiday Entitlement Id for the required request.*/
		
		$date = fix_date(date(DATE_FORMAT));
		$holidayEntitlement = DataObjectFactory::Factory('Holidayentitlement');
		if (isset($this->_data['Holidayrequest']['start_date'])){
            $date = $this->_data['Holidayrequest']['start_date'];
		}
		
		$holidayEntitlement->loadEntitlement($this->_data[$this->modeltype]['employee_id'], $date);
		$days_left = $holidayEntitlement->get_total_days_left($date, $this->_data[$this->modeltype]['employee_id']);
		
		//Compare the days left with the requested number of days.

		$holidayRequest = $this->_templateobject;
		
		$db = DB::Instance();
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('employee_id', '=', $this->_data[$this->modeltype]['employee_id']));
		$cc->add(new Constraint('status', 'in', '(' . $db->qstr($holidayRequest->newRequest()) . ',' . $db->qstr($holidayRequest->authorise()) .')'));
		
		if (!empty($this->_data[$this->modeltype]['id']))
		{
			$holidayRequest->load($this->_data[$this->modeltype]['id']);
			$days_left += $holidayRequest->num_days;
			$cc->add(new Constraint('id', '!=', $holidayRequest->id));
		}
		
		if (isset($this->_data[$this->modeltype]['start_date'])
			&& empty($this->_data[$this->modeltype]['start_date']))
		{
			$errors[] = 'You must enter a start date';
		}
		
		if (isset($this->_data[$this->modeltype]['end_date'])
			&& empty($this->_data[$this->modeltype]['end_date']))
			{
			$errors[] = 'You must enter a start date';
		}
		
		if (isset($this->_data[$this->modeltype]['start_date'])
			&& isset($this->_data[$this->modeltype]['end_date'])
			&& count($errors) == 0
			&& strtotime(fix_date($this->_data[$this->modeltype]['start_date'])) > strtotime(fix_date($this->_data['Holidayrequest']['end_date'])))
		{
			$errors[] = 'End Date cannot be before Start Date';
		}
		
		if(count($errors) == 0)
		{
			$cc->add(new Constraint($db->qstr(fix_date($this->_data[$this->modeltype]['start_date'])), 'between', 'start_date and end_date'));
			$cc->add(new Constraint($db->qstr(fix_date($this->_data[$this->modeltype]['end_date'])), 'between', 'start_date and end_date'));
		}
		
		if ($holidayRequest->getCount($cc) > 0)
		{
			$errors[] = 'This request overlaps an existing request';
		}
		
		if($days_left < $this->_data[$this->modeltype]['num_days'] && !isset($this->_data['Holidayrequest']['special_circumstances']))
		{
			$errors[] = 'You do not have any days left or your request is not within an entitlement period!';
		}
		
		if(count($errors) > 0 || !parent::save($this->modeltype))
		{
			$flash->addErrors($errors);
			$this->_data['employee_id'] = $this->_data[$this->modeltype]['employee_id'];
			$this->refresh();
		}
		else
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
	}
	
	public function authorise_request()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
				
		$holidayRequest = $this->_uses[$this->modeltype];
		
		$holidayRequest->approved_by = $this->get_employee_id();
		$holidayRequest->status = $holidayRequest->authorise();
		
		if(!$holidayRequest->save())
		{
			$flash = Flash::Instance();
			$flash->addErrors('Error authorising holiday');
			$this->refresh();
		}
		else
		{
			sendTo('employees'
				  ,'view'
				  ,$this->_modules
				  ,array('id' => $holidayRequest->employee_id));
		}
				
	}

	public function decline_request()
	{
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		parent::_new();
		
		$holidayRequest = $this->_uses[$this->modeltype];
		
		$holidayRequest->status = $holidayRequest->decline();
		
		$this->view->set('authoriser', $this->get_employee_id());
		
	}

	public function view()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
				
		$holidayRequest = $this->_uses[$this->modeltype];
		
		$employee_id = $holidayRequest->employee_id;
		
		$holidayEntitlement = DataObjectFactory::Factory('Holidayentitlement');
		$date = fix_date(date(DATE_FORMAT));
		
		$days_left = $holidayEntitlement->get_total_days_left($date, $employee_id);
		$this->view->set('days_left',$days_left);
		
		$employee = DataObjectFactory::Factory('Employee');
		$employee->load($holidayRequest->employee_id);
		$this->view->set('employee',$employee);
		
		$currently_viewing = $employee->employee.': '.$holidayRequest->start_date.'-'.$holidayRequest->end_date;
		
		$sidebarlist = array();
		
		$sidebarlist[$employee->employee] = array('tag' => $currently_viewing
											   ,'link'=> array('modules'=>$this->_modules
															  ,'controller'=>$this->name
															  ,'action'=>'view'
															  ,'id'=>$holidayRequest->id
														)
		);
		
		if ($holidayRequest->awaitingAuthorisation()
			|| ($holidayRequest->authorised()
				&& $holidayRequest->employee_id != $this->get_employee_id()) )
		{
			$sidebarlist['cancel'] = array('tag' => 'Cancel Request'
										  ,'link'=> array('modules'=>$this->_modules
														 ,'controller'=>$this->name
														 ,'action'=>'delete'
														 ,'id'=>$holidayRequest->id
												  )
			);
		}
		
		if ($holidayRequest->awaitingAuthorisation())
		{
			$sidebarlist['edit'] = array('tag' => 'Change Request'
									    ,'link'=> array('modules'=>$this->_modules
													   ,'controller'=>$this->name
													   ,'action'=>'edit'
													   ,'id'=>$holidayRequest->id
												)
			);
			
			$authorisers = $employee->getAuthorisers($employee->holiday_model());
			
// Holiday can only be authorised by an 'authoriser'
			if (in_array($this->get_employee_id(), $authorisers))
			{
				$sidebarlist['confirm'] = array('tag' => 'Confirm Request'
										,'link'=> array('modules'=>$this->_modules
													   ,'controller'=>$this->name
													   ,'action'=>'authorise_request'
													   ,'id'=>$holidayRequest->id
												)
				);
				
				$sidebarlist['decline'] = array('tag' => 'Decline Request'
										,'link'=> array('modules'=>$this->_modules
													   ,'controller'=>$this->name
													   ,'action'=>'decline_request'
													   ,'id'=>$holidayRequest->id
												)
				);
			}
		}
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList('currently_viewing', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	/*
	 * Ajax Functions
	 */
	public function getHolidays()
	{
		$holidays = new HolidayRequestCollection();
		$sh = new SearchHandler($holidays,false);
		$cc = new ConstraintChain();
		$cc->add(new Constraint('end_date', '>=', date('Y-m-d H:i:s', $this->_data['start'])));
		$cc->add(new Constraint('start_date', '<', date('Y-m-d H:i:s', $this->_data['end'])));
		if (isset($this->_data['employee_id'])) {
			$cc->add(new Constraint('employee_id', '=', $this->_data['employee_id']));
		}
		$sh->addConstraintChain($cc);
		$holidayrequests = $holidays->load($sh, '', RETURN_ROWS);

		$output_events=array();
		$colours=array('A'=>'fc_green',
					   'C'=>'fc_grey',
					   'D'=>'fc_red',
					   'W'=>'fc_yellow'
		);
		$accessobject = AccessObject::Instance();
		$access_allowed = $accessobject->hasPermission('hr','holidayrequests','edit');
		$current_employee = $this->get_employee_id();
		
		foreach($holidayrequests as $key=>$value)
		{
			$employee = DataObjectFactory::Factory('Employee');
			
			$employee->authorisationPolicy($employee->holiday_model());
			
			// Employee will not load if user does not have authorisation
			$employee->load($value['employee_id']);
			
			$authorisers = $employee->getAuthorisers($employee->holiday_model());
			
			$authoriser = (in_array($current_employee, $authorisers));
			
			// Also need to check user's permissions for each holiday request
			// as to whether they can edit the requests for the employee
			$editable = false;
			if ($value['status'] == 'W') {
				$editable = ($employee->isLoaded() && $access_allowed && ($authoriser || $value['status'] == 'W'));
			}
			
			//echo $value['employee'].' start_date:'	.$value['start_date'].' status:'.$value['status'].' all_day:'.$value['all_day'];		
			$output_events[]=array('id'=>$value['id'],
							'title'=>$value['employee'],
							'allDay'=>($value['all_day']=='t'),
							'start'=>strtotime($value['start_date']),	
							'end'=>strtotime($value['end_date'].' 18:00:00'),
							'className'=>$colours[$value['status']],
							'employee_id'=>$value['employee_id'],
							'status'=>$value['status'],
							'editable'=>$editable,
							'authoriser'=>$authoriser,
							'reason_declined'=>$value['reason_declined']
					  );
		}
	
		echo json_encode($output_events);
		exit;
		
	}
	
	public function getDaysLeft ($_employee_id = '')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['employee_id'])) { $_employee_id=$this->_data['employee_id']; }
		}
		
		$entitlement = DataObjectFactory::Factory('Holidayentitlement');
		
		$date = fix_date(date(DATE_FORMAT));
		
		$entitlement->loadEntitlement($_employee_id, $date);
		
		if ($entitlement->isLoaded() && $entitlement->num_days > 0)
		{
			$days_left = $entitlement->get_total_days_left($date, $_employee_id);
		}
		else
		{
			$days_left = 0;
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$days_left);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $days_left;
		}
		
	}
	
	public function getNumberDays($_employee_id = '', $_start_date = '', $_end_date = '', $_all_day = true)
	{
		// Currently does not use employee id - future extension to include the following:-
		//	1)	get the days the employee works
		//	2)	check if employee holiday entitlement includes/excludes statutory holidays
		//	3)	get bank holiday calendar if need to exclude statutory days from request
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['employee_id'])) { $_employee_id=$this->_data['employee_id']; }
			if(!empty($this->_data['start_date'])) { $_start_date=$this->_data['start_date']; }
			if(!empty($this->_data['end_date'])) { $_end_date=$this->_data['end_date']; }
			if(!empty($this->_data['all_day'])) { $_all_day=($this->_data['all_day']=='t'); }
		}
		
		$_start_date	= fix_date($_start_date);
		$_end_date		= fix_date($_end_date);

		$date = strtotime($_start_date);
		
		for ($date = strtotime($_start_date), $days = 0; $date <= strtotime($_end_date); $date = strtotime(' +1 day', $date))
		{
			if (date("N",$date) < 6)
			{
				$days++;
			}
		}
		
		if (!$_all_day)
		{
			$days = bcdiv($days, 2, 1);
		}
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$days);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $days;
		}
	}

	public function updateEvent()
	{
		echo json_encode($this->_data);
		exit();
	}
	
	/*
	 * Private Functions
	 */
}

// End of HolidayrequestsController
