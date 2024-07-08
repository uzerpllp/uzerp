<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeepayhistorysController extends printController
{

	protected $version = '$Revision: 1.19 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EmployeePayHistory');
		
		$this->uses($this->_templateobject);
	}
	
	/*
	 * Standard index listing of Employee Pay History
	 */
	public function index($collection = null, $sh = '', &$c_query = null)
	{
		
		$errors = array();
		
		$s_data = array();
		
		if (!empty($this->_data['employee_id'])) { $s_data['employee_id'] = $this->_data['employee_id']; }
		if (!empty($this->_data['employee_pay_periods_id'])) { $s_data['employee_pay_periods_id'] = $this->_data['employee_pay_periods_id']; }
		if (!empty($this->_data['hours_type_id'])) { $s_data['hours_type_id'] = $this->_data['hours_type_id']; }
		
		// Set context from calling module
		$this->setSearch('employeeSearch', 'payHistory', $s_data);
		
		parent::index(new EmployeePayHistoryCollection());
		
//		$sidebar = new SidebarController($this->view);
//		
//		$sidebarlist = array();
//		
//		$sidebarlist['new'] = array(
//					'tag' => 'Enter Payments',
//					'link' => array('modules'	=> $this->_modules
//								   ,'controller'=> $this->name
//								   ,'action'	=> 'new')
//		);
//		
//		$sidebar->addList('Actions', $sidebarlist);
//		
//		$this->view->register('sidebar',$sidebar);
//		
//		$this->view->set('sidebar',$sidebar);
//		
		$this->view->set('clickaction', 'none');
	}	
	
	/*
	 * Delete of Employee Pay History is not allowed
	 */
	public function delete($modelName = null)
	{
		// Deletion is not allowed
		sendBack();
	}
	
	/*
	 * Enter a new Employee Pay History
	 */
	public function _new()
	{
		
		$flash = Flash::Instance();
		
		parent::_new();
		
		$pay_history = $this->_uses[$this->modeltype];
	
		if (!empty($this->_data['employee_pay_periods_id'])){
			$current_pay_period_id		= $this->_data['employee_pay_periods_id'];
			$period_start_date = $this->getPayPeriodStartDate($current_pay_period_id);
			$period_end_date = $this->getPayPeriodEndDate($current_pay_period_id);
			// restrict selection to just the current period by sending an empty periods array to the template
			// display the period dates in template title
			$employee_pay_periods = array();
			$startdate = new DateTime($period_start_date);
			$enddate = new DateTime($period_end_date);
			$this->_templateobject->setTitle(' payments - '. $startdate->format('jS M Y') .' to '. $enddate->format('jS M Y'));
		}
		else{
			// get all the open periods
			$employee_pay_periods = $this->getOpenPeriods();
			ksort($employee_pay_periods);
			reset($employee_pay_periods);
			// current period is the first open period
			$current_pay_period_id		= key($employee_pay_periods);
			$period_start_date = $this->getPayPeriodStartDate($current_pay_period_id);
		}

		$employees					= $this->getEmployeesForPeriod($current_pay_period_id);
		$pay_history->employee_id	= $employee_id = (empty($this->_data['employee_id']))?key($employees):$this->_data['employee_id'];
/*
		This is not required anymore... leaving just in case it wants to be added back

		$period_start_date			= $pay_history->getLatestPeriodStart($employee_id);
		
		// Need to get the earliest pay period that has no employee pay history
		$cc = new ConstraintChain();
		$cc->add(new Constraint('employee_id', '=', $employee_id));
		
		if (!empty($this->_data['employee_pay_periods_id']))
		{
			$current_pay_period_id	= $this->_data['employee_pay_periods_id'];
		}
		else
		{
			$idField = $pay_history->idField;
			$pay_history->idField = $pay_history->identifierField = 'employee_pay_periods_id';
			
			$paid_periods = $pay_history->getAll($cc);
			
			foreach ($employee_pay_periods as $id=>$period)
			{
				if (!isset($paid_periods[$id]))
				{
					$current_pay_period_id = $id;
					break;
				}
			}
			
			$pay_history->idField = $idField;
		}
*/		
		$this->view->set('employees', $employees);
		$this->view->set('employee_pay_periods', $employee_pay_periods);
		$this->view->set('current_pay_period', $current_pay_period_id);
		$this->view->set('period_start_date', $period_start_date);
		
		$output = $this->getPayHistoryData($employee_id);
		
// TODO: Allow edit of existing Employee Pay History entries
// if the Pay Period is not closed

// Probably need to get the list of current entries for the employee/pay period
// and overwrite the values in the array returned by getPayPeriodData
		$this->getPayPeriodData($employee_id, $current_pay_period_id, $period_start_date);
		
	}
	
	/*
	 * Edit Employee Pay History
	 * 
	 * Rules:
	 * 	change end date allowed
	 * 	change rate only allowed if start date in future
	 */
	public function edit ()
	{
		$flash = Flash::Instance();
		
		$flash->addError('Edit is not allowed');
		
		sendBack();
		
	}
	
	/*
	 * Save the Employee Pay History
	 */
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$errors = array();
		$flash = Flash::Instance();
		
		$pay_data['employee_id']				= $this->_data[$this->modeltype]['employee_id'];
		$pay_data['employee_pay_periods_id']	= $this->_data[$this->modeltype]['employee_pay_periods_id'];
		
		$pay_histories = array();
		
		foreach ($this->_data[$this->modeltype] as $field=>$data)
		{
			if (is_array($data))
			{
				if (!empty($data['pay_units']) || $data['allow_zero_units']=='t')
				{
					$pay_data['id']					= $data['id'];
					$pay_data['hours_type_id']		= $data['hours_type_id'];
					$pay_data['payment_type_id']	= $data['payment_type_id'];
					$pay_data['pay_frequency_id']	= $data['pay_frequency_id'];
					$pay_data['pay_units']			= $data['pay_units'];
					$pay_data['pay_rate']			= $data['pay_rate'];
					$pay_data['comment']			= $data['comment'];
						
					$pay_histories[] = DataObject::Factory($pay_data, $errors, $this->modeltype);
				}
				elseif (!empty($data['id']))
				{
					$payhistory = DataObjectFactory::Factory($this->modeltype);
					$payhistory->delete($data['id'], $errors);
				}
			}
		}
		
		$db = DB::Instance();
			
		if (empty($pay_histories))
		{
			$errors[] = 'No data to save';
		}
		else
		{
			foreach ($pay_histories as $pay_history)
			{
				if (!$pay_history || !$pay_history->save())
				{
					$errors[] = 'Error saving pay history : '.$db->ErrorMsg();
					
					$db->FailTrans();
					
					break;
				}
			}
			
		}
		
		$db->completeTrans();
		
		if( count($errors)==0)
		{
			if (isset($this->_data['saveAnother']))
			{
				$employee = DataObjectFactory::Factory('Employee');
				
				$employee->load($this->_data[$this->modeltype]['employee_id']);
				
				$employee->orderby = 'employee';
				
				$cc = new ConstraintChain();
				$cc->add(new Constraint('employee', '>', $employee->employee));
				
				$cc1 = new ConstraintChain();
				$cc1->add(new Constraint('finished_date', 'is', 'NULL'));
				$cc1->add(new Constraint('finished_date', '>=', $this->getPayPeriodStartDate($this->_data[$this->modeltype]['employee_pay_periods_id'])), 'OR');
				
				$cc->add($cc1);
				
				$employee->authorisationPolicy();
		
				$employees = $employee->getAll($cc, TRUE, TRUE);
				
				$next_employee = key($employees);
				
				if (!empty($next_employee))
				{
					sendTo($this->name
						  ,'new'
						  ,$this->_modules
						  ,array('employee_id'=>$next_employee, 'employee_pay_periods_id'=>$this->_data[$this->modeltype]['employee_pay_periods_id']));
				}
			}
			
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$flash = Flash::Instance();
		$flash->addErrors($errors);
		
		$this->refresh();

	}
	
	public function view_employee()
	{
	
		$s_data = array();
	
		if (isset($this->_data['employee_id'])) { $s_data['employee_id'] = $this->_data['employee_id']; }
		// Set context from calling module
		$this->setSearch('employeeSearch', 'employeePayHistory', $s_data);
	
		parent::index(new EmployeePayHistoryCollection($this->_templateobject));
	
		$this->view->set('clickaction', 'none');
	
	}
	
	/*
	 * Ajax Functions
	 */
	
	public function getPayPeriodData($_employee_id = '', $_employee_pay_periods_id = '', $_period_start_date = '')
	{
		// Need to construct a data structure as follows:-
		// 1) Get existing Employee Pay History rows for the supplied employee/pay_period
		// 2) Get Employee Rate rows that are current for the supplied pay_period - merge with (1)
		// 3) Get Hour Types not linked to payment types - merge with (1)
		if(isset($this->_data['ajax']))
		{
				if(!empty($this->_data['employee_id'])) {	$_employee_id=$this->_data['employee_id']; }
				if(!empty($this->_data['employee_pay_periods_id'])) {	$_employee_pay_periods_id = $this->_data['employee_pay_periods_id']; }
		}
		
		if (empty($_employee_id) && empty($_employee_pay_periods_id) && empty($_period_start_date))
		{
			$this->view->set('hour_types', array());
			return;
		}
		
		$pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		$pay_period->load($_employee_pay_periods_id);
		
		if ($pay_period->isLoaded())
		{
			$_period_start_date = $pay_period->period_start_date;
		}
		
		$hour_types = array();
		
		// Get any current Pay History rows for the employee/pay_period
		
		$fields = $this->_templateobject->getDisplayFieldNames();
		$fields['allow_zero_units'] = 'allow_zero_units';
		
		$this->_templateobject->setDefaultDisplayFields($fields);
		$this->_templateobject->setDisplayFields();
		
		$employee_pay_histories = new EmployeePayHistoryCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($employee_pay_histories);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $_employee_id));
		$sh->addConstraint(new Constraint('employee_pay_periods_id', '=', $_employee_pay_periods_id));
		
		$employee_current_history = $employee_pay_histories->load($sh, '', RETURN_ROWS);
		
		// Initialise the hours_type array with existing values
		if (count($employee_current_history) > 0)
		{
			foreach ($employee_current_history as $pay_history)
			{
				if (!empty($pay_history['payment_type_id']) && !empty($pay_history['hours_type_id']))
				{
					$key = $pay_history['payment_type_id'].'-'.$pay_history['hours_type_id'];
				}
				elseif (empty($pay_history['payment_type_id']))
				{
					$key = $pay_history['hours_type_id'];
				}
				else
				{
					$key = $pay_history['payment_type_id'].'-0';
				}
				
				$hour_types[$key]					= $pay_history;
				$hour_types[$key]['default_units']	= $pay_history['pay_units'];
				$hour_types[$key]['rate_value']		= $pay_history['pay_rate'];
				$hour_types[$key]['units_variable']	= 't';
				$hour_types[$key]['rate_variable']	= 't';
				unset($hour_types[$key]['pay_units']);
				unset($hour_types[$key]['pay_rate']);
				unset($hour_types[$key]['period_start_date']);
				unset($hour_types[$key]['period_end_date']);
				unset($hour_types[$key]['tax_year']);
				unset($hour_types[$key]['tax_month']);
				unset($hour_types[$key]['tax_week']);
				unset($hour_types[$key]['calendar_week']);
				unset($hour_types[$key]['pay_basis']);
				unset($hour_types[$key]['pay_value']);
				
			}
		}
		
		// get distinct list of hour types that are linked to payment types
		$hour_payment_type	= DataObjectFactory::Factory('HourPaymentType');
		
		$hour_payment_type->idField			= 'hours_type_id';
		$hour_payment_type->identifierField	= 'payment_type_id';
		
		$hour_payment_types = $hour_payment_type->getAll();
		
		// Get Employee Rate rows that are current for the supplied pay_period
		$employee_rate_hours = new EmployeeRateCollection();
		$employee_rate_hours->setViewName('employee_rate_hours_overview');
		
		$sh = $this->setSearchHandler($employee_rate_hours);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $_employee_id));
		
		$sh->addConstraintChain(currentDateConstraint($_period_start_date));
		
		$sh->setFields(array("payment_type_id||'-'||coalesce(hours_type_id,0) as id"
							,'payment_type'
							,'payment_type_id'
							,'hours_type'
							,'hours_type_id'
							,'pay_frequency'
							,'pay_frequency_id'
							,'employee_id'
							,'employee'
							,'default_units'
							,'allow_zero_units'
							,'units_variable'
							,'rate_value'
							,'rate_variable'));
		
		$sh->setOrderby('position');
		
		$rate_hours = $employee_rate_hours->load($sh, '', RETURN_ROWS);
		
		if (!empty($rate_hours))
		{
			foreach ($rate_hours as $employee_rate_hour)
			{
				if (!empty($employee_rate_hour['hours_type_id']))
				{
					// Hours type exists for employee rate 
					$hour_payment_types[$employee_rate_hour['hours_type_id']] = $employee_rate_hour['payment_type_id'];
				}
				
				if (isset($hour_types[$employee_rate_hour['id']]))
				{
					$hour_types[$employee_rate_hour['id']]['units_variable'] = $employee_rate_hour['units_variable'];
					$hour_types[$employee_rate_hour['id']]['rate_variable'] = $employee_rate_hour['rate_variable'];
				}
				else
				{
					unset($employee_rate_hour['created']);
					unset($employee_rate_hour['createdby']);
					unset($employee_rate_hour['lastupdated']);
					unset($employee_rate_hour['alteredby']);
					unset($employee_rate_hour['usercompanyid']);
					unset($employee_rate_hour['position']);
					$hour_types[$employee_rate_hour['id']] = $employee_rate_hour;
//					unset($hour_types[$employee_rate_hour['id']]['id']);
					$hour_types[$employee_rate_hour['id']]['id'] = '';
				}
			}
		}
		
		$hour_type = DataObjectFactory::Factory('HourType');
		$hour_type->orderby = 'position';
		
		$cc = new ConstraintChain();
		
		if (count($hour_payment_types) > 0)
		{
			// Only interested in hour types not linked to payment types
			$cc->add(new Constraint($hour_type->idField, 'NOT IN', '(' . implode(',', array_keys($hour_payment_types)) . ')'));
		}
		
		foreach ($hour_type->getAll($cc) as $hour_type_id=>$hour_type)
		{
			if (!isset($hour_types[$hour_type_id]))
			{
				$hour_types[$hour_type_id]['id']				= '';
				$hour_types[$hour_type_id]['hours_type_id']		= $hour_type_id;
				$hour_types[$hour_type_id]['hours_type']		= $hour_type;
				$hour_types[$hour_type_id]['payment_type_id']	= '';
				$hour_types[$hour_type_id]['payment_type']		= '';
				$hour_types[$hour_type_id]['employee_id']		= $_employee_id;
				$hour_types[$hour_type_id]['default_units']		= 0;
				$hour_types[$hour_type_id]['rate_value']		= 0.00;
			}
			$hour_types[$hour_type_id]['units_variable']	= 't';
			$hour_types[$hour_type_id]['rate_variable']		= 't';
		}
		
		$this->view->set('hour_types', $hour_types);
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('model', $this->_templateobject);
			$html = $this->view->fetch($this->getTemplateName('hour_types'));
			$output['hour_types']=array('data'=>$html,'is_array'=>FALSE);
						
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		
	}
	
	public function getPayHistoryData($_employee_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
				if(!empty($this->_data['employee_id'])) {	$_employee_id=$this->_data['employee_id']; }
		}
		
		if (empty($_employee_id) && empty($_period_start_date))
		{
			return array('current_rates'=>array('data'=>'','is_array'=>FALSE));
		}
		
		$collection = new EmployeePayHistoryCollection();
		
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $_employee_id));
		
		$fields = array("tax_year||' - '||tax_week as id"
					   ,'employee'
					   ,'period_start_date'
					   ,'period_end_date'
					   ,'pay_basis'
					   ,'tax_year'
					   ,'tax_week');
		
		$sh->setGroupBy($fields);
		
		$sh->setOrderby(array('tax_year','tax_week'), array('DESC', 'DESC'));
		
		$fields[] = 'sum(pay_value) as pay_value';
		
		$sh->setFields($fields);
		
		$db = DB::Instance();
		
		$this->view->set('clickaction', 'view');
		
		parent::index($collection, $sh);
		
		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		$this->view->set('no_ordering', true);
		$this->view->set('clickaction', 'none');
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('collection', $collection);
			$system=system::Instance();
			
			// This is a hack for embedded ajax call - should handle paging better
			$this->view->set('paging_link', getParamsArray());
			$current_rates = $this->view->fetch('datatable');
			$output['current_rates']=array('data'=>$current_rates,'is_array'=>FALSE);
						
			$this->view->set('data',$output);
			$this->setTemplateName('ajax_multiple');
		}
		else
		{
			return array();
		}
		
	}
	
	public function getEmployeesForPeriod($_employee_pay_periods_id='')
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['employee_pay_periods_id'])) {	$_employee_pay_periods_id = $this->_data['employee_pay_periods_id']; }
		}

		$employee = DataObjectFactory::Factory('employee');
		$employee->orderby = 'employee';
		$employee->authorisationPolicy();

		//Get current employees and leavers in current period
		$cc = new ConstraintChain();
		
		$cc->add(new Constraint('pay_basis', '=' , $this->getPayPeriodPayBasis($_employee_pay_periods_id) ));
		$cc->add(new Constraint('finished_date', 'is', 'NULL'));
		$cc->add(new Constraint('finished_date', '>=', $this->getPayPeriodStartDate($_employee_pay_periods_id)), 'OR');

		$employees = $employee->getAll($cc, TRUE, TRUE);

		if(isset($this->_data['ajax']))
		{
			$this->view->set('options', $employees);
            $this->setTemplateName('select_options');
		}
		else
		{
			return $employees;
		}
		
	}


	/*
	 * Private Functions
	 */

	private function getOpenPeriods()
	{
		$employee_pay_periods = new EmployeePayPeriodCollection(DataObjectFactory::Factory('EmployeePayPeriod'));
		
		$sh = new SearchHandler($employee_pay_periods, false);
		
		$sh->addConstraint(new Constraint('closed', 'is', FALSE));
		
		$open_periods = array();
				
		$the_pay_periods = $employee_pay_periods->load($sh, '', RETURN_ROWS);

		foreach ($the_pay_periods as $pay_period)
		{
			$startdate = new DateTime($pay_period['period_start_date']);
			$enddate = new DateTime($pay_period['period_end_date']);
			if ($pay_period['pay_basis']=='W')  {
				$basis = 'Week';
			}
			else {
				$basis = 'Month';
			}

			$open_periods[$pay_period['id']] = $basis.' '.$startdate->format('jS M Y') .' to '. $enddate->format('jS M Y');
		}

		return $open_periods;

	}
	
	private function getPayPeriodStartDate($_pay_period_id)
	{
		
		$employee_pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		
		$employee_pay_period->load($_pay_period_id);
		
		return $employee_pay_period->period_start_date;
	}

	private function getPayPeriodEndDate($_pay_period_id)
	{
		
		$employee_pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		
		$employee_pay_period->load($_pay_period_id);
		
		return $employee_pay_period->period_end_date;
	}

	private function getPayPeriodPayBasis($_pay_period_id)
	{
		
		$employee_pay_period = DataObjectFactory::Factory('EmployeePayPeriod');
		
		$employee_pay_period->load($_pay_period_id);
		
		return $employee_pay_period->pay_basis;
	}
	
}

// End of EmployeepayhistorysController
