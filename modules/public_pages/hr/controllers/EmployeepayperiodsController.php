<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeepayperiodsController extends Controller {

	protected $version = '$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module = null, $action = null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EmployeePayPeriod');
		
		$this->uses($this->_templateobject);
		
	}
	
	/*
	 * Standard index listing of Employee Pay Period
	 */
	public function index()
	{
		
		
		$errors = array();
		
		$s_data = array();
		
		// Set context from calling module
		$this->setSearch('employeeSearch', 'payPeriods', $s_data);
		
		parent::index(new EmployeePayPeriodCollection($this->_templateobject));

		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['new'] = array(
					'tag' => 'New Pay Period',
					'link' => array('modules'	=> $this->_modules
								   ,'controller'=> $this->name
								   ,'action'	=> 'new')
		);
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
		$this->view->set('clickaction', 'view');
		
	}	
	
	/*
	 * Delete of Employee Pay Period is not allowed
	 */
	public function delete()
	{
		// Deletion is not allowed
		sendBack();
	}
	
	/*
	 * Enter a new Employee Pay Period
	 */
	public function _new()
	{
		
		$flash = Flash::Instance();
		
		parent::_new();
		
		$pay_period = $this->_uses[$this->modeltype];
		
		if ($pay_period->isLoaded())
		{
			$period_start_date	= $pay_period->period_start_date;
		}
		else
		{
//			$pay_basis = key($pay_period->getEnumOptions('pay_basis'));
			
//			$pay_history->period_start_date	= $period_start_date = $pay_period->getNextPeriodStart($pay_basis);
//			$pay_history->period_end_date	= $this->getPeriodEndDate($pay_period->period_start_date, $pay_basis);
			
			$pay_period->getLatestPeriod();
			
			if ($pay_period->isLoaded())
			{
				$pay_period->{$pay_period->idField}	= '';
				$pay_period->period_start_date		= $pay_period->period_end_date;
				$pay_period->period_end_date		= $this->getPeriodEndDate($pay_period->period_start_date, $pay_period->pay_basis);
				$pay_period->tax_year				= $pay_period->tax_year;
				$pay_period->tax_month				= $pay_period->tax_month;
				$pay_period->tax_week				= $pay_period->tax_week+1;
			}
		}
		
	}
	
	/*
	 * Close the Employee Pay Period
	 * 
	 */
	public function close_period ()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$pay_period = $this->_uses[$this->modeltype];
		
		$pay_period->closed = TRUE;
		
	}
	
		/*
	 * Save the Employee Pay Period
	 */
	public function save()
	{
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$errors = array();
		$flash = Flash::Instance();
		
		$data = $this->_data[$this->modeltype];
		
		if (isset($data['pay_basis']))
		{
			if (strtolower($data['pay_basis']) == 'm')
			{
				if (date('j', strtotime(fix_date($data['period_start_date']))) != 1)
				{
					$errors[] = 'Period must start on first day of month';
				}
			}
			else
			{
				$params = DataObjectFactory::Factory('HRParameters');
				
				$params->load($errors);
				if ($params->isLoaded() && date('I', strtotime(fix_date($data['period_start_date']))) != $params->week_start_day)
				{
					$errors[] = 'Period must start on a '.$params->week_start_day;
				}
			}
		}
		
		foreach (array('period_start_date','period_end_date') as $fieldname)
		{
			if (isset($this->_data[$this->modeltype][$fieldname.'_hours']) && isset($this->_data[$this->modeltype][$fieldname.'_minutes']))
			{
				$this->_data[$this->modeltype][$fieldname] .= ' ' . $this->_data[$this->modeltype][$fieldname.'_hours']
															. ':' . $this->_data[$this->modeltype][$fieldname.'_minutes'];
			}
		}
		
		if( count($errors)==0 && parent::save($this->modeltype, $this->_data, $errors))
		{
			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
		$flash = Flash::Instance();
		$flash->addErrors($errors);
		
		$this->refresh();

	}
	
	public function view ()
	{
		
		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		
		$pay_period = $this->_uses[$this->modeltype];
		
		if (isset($this->_data['employee_id']))
		{
			$employee_id = $this->_data['employee_id'];
		}
		else
		{
			$employee_id = '';
		}
		
		$output = $this->getPayHistoryData($employee_id, $pay_period->{$pay_period->idField});
		
		$sidebar = new SidebarController($this->view);
		
		$sidebarlist = array();
		
		$sidebarlist['viewall'] = array(
				'tag' => 'View All Pay Periods',
				'link' => array('modules'		=> $this->_modules
							   ,'controller'	=> $this->name
							   ,'action'		=> 'index')
		);
		
		// TODO: Some additional validation:-
		//	1)	only close off the period if the previous period is closed
		if ($pay_period->closed == 'f')
		{
			$sidebarlist['enterpayments'] = array(
					'tag' => 'Enter Payments for Period',
					'link' => array('modules'				=> $this->_modules
								   ,'controller'			=> 'employeepayhistories'
								   ,'action'				=> 'new'
								   ,$pay_period->idField	=> $pay_period->{$pay_period->idField})
			);
			$sidebarlist['closeperiod'] = array(
				'tag' => 'Close Period',
				'link' => array('modules'				=> $this->_modules
							   ,'controller'			=> $this->name
							   ,'action'				=> 'close_period'
							   ,$pay_period->idField	=> $pay_period->{$pay_period->idField})
		);
		}
		
		$sidebar->addList('Actions', $sidebarlist);
		
		$this->view->register('sidebar',$sidebar);
		
		$this->view->set('sidebar',$sidebar);
		
	}
	
	/*
	 * Ajax Functions
	 */
	
	public function getPayHistoryData($_employee_id = '', $_employee_pay_periods_id = '')
	{
		
		if(isset($this->_data['ajax']))
		{
				if(!empty($this->_data['employee_id'])) {	$_employee_id=$this->_data['employee_id']; }
				if(!empty($this->_data['employee_pay_periods_id'])) {	$_employee_pay_periods_id=fix_date($this->_data['period_start_date']); }
		}
		
		if (empty($_employee_id) && empty($_employee_pay_periods_id))
		{
			return array('current_rates'=>array('data'=>'','is_array'=>FALSE));
		}
		
		$errors = array();
		
		$s_data = array();
		
		if (!empty($_employee_id)) { $s_data['employee_id'] = $_employee_id; }
		
		// Set context from calling module
		$this->setSearch('employeeSearch', 'payPeriodHistory', $s_data);
		
		$collection = new EmployeePayHistoryCollection();
		
		$sh = $this->setSearchHandler($collection);
		
		// Need to set employee_id as id field as well as selecting employee_id
		$fields = array('employee_id as id'
					   ,'employee_id'
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
		
		if (!empty($_employee_id))
		{
			$sh->addConstraint(new Constraint('employee_id', '=', $_employee_id));
		}
		
		$db = DB::Instance();
		
		if (!empty($_employee_pay_periods_id))
		{
			$sh->addConstraint(new Constraint('employee_pay_periods_id', '=', $_employee_pay_periods_id));
		}
		
		$this->view->set('clickaction', 'view');
		
		parent::index($collection, $sh);
		
		// could we return the data as an array here? save having to re use it in the new / edit?
		// do a condition on $ajax, and return the array if false
		$this->view->set('no_ordering', true);
		$this->view->set('clickaction', 'none');
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('collection', $collection);
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
	
	public function getPeriodEndDate($_period_start_date, $_pay_basis)
	{
		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['pay_basis'])) { $_pay_basis=$this->_data['pay_basis']; }
			if(!empty($this->_data['period_start_date'])) { $_period_start_date=fix_date($this->_data['period_start_date']); }
		}
		
		if (empty($_period_start_date) || empty($_pay_basis))
		{
			return '';
		}
		
		$basis = (strtolower(substr($_pay_basis, 0, 1)) == 'm')?'+1month':'+1week';
		
		$period_end_date = date(DATE_TIME_FORMAT, strtotime($_period_start_date.$basis));
		
		if(isset($this->_data['ajax']))
		{
			$this->view->set('value',$period_end_date);
			$this->setTemplateName('text_inner');
		}
		else
		{
			return $period_end_date;
		}
				
	}
	
	/*
	 * Private Functions
	 */
	
}

// End of EmployeepayperiodsController
