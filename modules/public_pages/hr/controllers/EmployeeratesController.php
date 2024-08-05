<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

/* TODO:
 * 
 * 1) Batch select/update of rates
 * 2) Edit of Rate
 *		can only edit latest
 *		can only set end date if null
 *		can only change end date if end date in future
 *		can only change rate if future start date
 */
class EmployeeratesController extends Controller
{

	protected $version='$Revision: 1.5 $';

	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{

		parent::__construct($module, $action);

		$this->_templateobject = DataObjectFactory::Factory('EmployeeRate');

		$this->uses($this->_templateobject);

	}

	/*
	 * Standard index listing of Employee Rates
	 */
	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$this->view->set('clickaction', 'none');

		parent::index(new EmployeeRateCollection($this->_templateobject));

	}	

	/*
	 * Delete of Employee Rates is not allowed
	 */
	public function delete($modelName = null)
	{
		// Deletion is not allowed
		sendBack();
	}

	/*
	 * Enter a new Employee Rate
	 */
	public function _new()
	{

		$flash = Flash::Instance();

		parent::_new();

		$ee_rate = $this->_uses[$this->modeltype];

		if ($ee_rate->isLoaded())
		{
			$this->_data['employee_id'] = $ee_rate->employee_id;

			$today = fix_date(date(DATE_FORMAT));

			if ($ee_rate->start_date > $today)
			{
				$this->view->set('future_dated', TRUE);
			}
			elseif (!is_null($ee_rate->end_date) && $ee_rate->end_date <= $today)
			{
				$flash->addError('Editing this entry is not allowed');
				sendBack();
			}
		}

		if (!$this->checkParams('employee_id'))
		{
			$flash->addError('Invalid Data - no employee selected');
			sendBack();
		}

		$employee = DataObjectFactory::Factory('employee');

		$employee->load($this->_data['employee_id']);

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

		$pay_type = DataObjectFactory::Factory('EmployeePaymentType');

		$payment_type_id = current(array_keys(($pay_type->getAll())));

		$output = $this->getRateTypeData($employee, $payment_type_id);

		if (!$ee_rate->isLoaded())
		{
			$ee_rate->start_date = $output['start_date']['data'];
		}

		if (!is_null($employee->finished_date))
		{
			if (is_null($ee_rate->end_date) || $ee_rate->end_date > $employee->finished_date)
			$ee_rate->end_date  = $employee->finished_date;
		}

		if (!is_null($employee->finished_date) && $employee->finished_date < $ee_rate->start_date)
		{
			$flash->addError('Employee is leaving');
			sendBack();
		}

	}

	/*
	 * Save the Employee Rate
	 */
	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}

		$current = $this->_uses[$this->modeltype];

		$errors = array();
		$flash = Flash::Instance();

		$data = $this->_data[$this->modeltype];

		$employee = DataObjectFactory::Factory('Employee');
		$employee->load($data['employee_id']);

		if (!$employee->isLoaded())
		{
			$errors[] = 'Cannot find employee details';
		}

		if ($current->isLoaded())
		{
			$this->validate_edit($current, $data, $employee, $errors);
		}
		else
		{
			$this->validate_new($data, $employee, $errors);
		}

		$this->_data['employee_id'] = $data['employee_id'];

		if( count($errors)==0 )
		{
			if (isset($this->_data['saveAnother']))
			{
				$this->saveAnother();
			}

			sendTo($_SESSION['refererPage']['controller']
				  ,$_SESSION['refererPage']['action']
				  ,$_SESSION['refererPage']['modules']
				  ,$_SESSION['refererPage']['other'] ?? null);
		}

		$flash = Flash::Instance();
		$flash->addErrors($errors);

		$this->refresh();

	}

	/*
	 * Bulk update of rates for employees
	 */
	public function review_rates()
	{

	}

	/*
	 * Saves the updated list of rates
	 */
	public function save_review_rates()
	{

	}

	public function view_employee()
	{

		$s_data = array();

		if (isset($this->_data['employee_id'])) { $s_data['employee_id'] = $this->_data['employee_id']; }
		// Set context from calling module
		$this->setSearch('employeeSearch', 'employeePayRates', $s_data);

		parent::index(new EmployeeRateCollection($this->_templateobject));

		$this->view->set('clickaction', 'edit');

	}

	/*
	 * Ajax Functions
	 */
	public function getRateTypeData($_employee = '', $_payment_type_id = '')
	{

		if(isset($this->_data['ajax']))
		{
			if(!empty($this->_data['employee_id']))
			{
				$_employee = DataObjectFactory::Factory('employee');
				$_employee->load($this->_data['employee_id']);
			}

			if(!empty($this->_data['payment_type_id'])) {	$_payment_type_id=$this->_data['payment_type_id']; }
		}

		if (empty($_employee) || empty($_payment_type_id))
		{
			return array('start_date'=>array('data'=>'','is_array'=>FALSE)
						,'current_rates'=>array('data'=>'','is_array'=>FALSE));
		}

		$collection = new EmployeeRateCollection($this->_templateobject);

		$sh = $this->setSearchHandler($collection);

		$sh->addConstraint(new Constraint('employee_id', '=', $_employee->id));
		$sh->addConstraint(new Constraint('payment_type_id', '=', $_payment_type_id));

		$this->view->set('clickaction', 'view');

		parent::index($collection, $sh);

		if ($collection->count() > 0)
		{
			$latest = $collection->current();

			if (!is_null($latest->end_date))
			{
				$start_date = date(DATE_FORMAT, strtotime($latest->end_date . ' + 1 day'));
			}
			elseif ($latest->start_date > fix_date(date(DATE_FORMAT)))
			{
				$start_date = date(DATE_FORMAT, strtotime($latest->start_date . ' + 1 day'));
			}
			else
			{
				$start_date = date(DATE_FORMAT);
			}
		}
		else
		{
			$start_date = un_fix_date($_employee->start_date);
		}

		$output['start_date']=array('data'=>$start_date,'is_array'=>FALSE);

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
			return $output;
		}

	}

	/*
	 * Private Functions
	 */
	private function validate_edit($current, $data, $employee, &$errors = array())
	{
		// TODO: Move to hr_controller as parent; see also EmployeecontractdetailsController

		// Check that the edit refers to the latest rate
		$db = DB::Instance();
		$db->StartTrans();

		if (!empty($data['end_date']))
		{
			$end_date = fix_date($data['end_date']);
		}

		$today = fix_date(date(DATE_FORMAT));

		if (!empty($data['end_date']) && $end_date < $current->start_date && $current->start_date > $today)
		{
			// New end date is before existing start date
			// and existing start date is in the future
			// so just delete it
			if (!$current->delete())
			{
				$errors[] = 'Error deleting rate : '.$db->ErrorMsg();
			}
			// If delete was successful, should the end date on the previous rate
			// be cleared here to make it current?
		}
		elseif (empty($data['end_date']) && $current->start_date < $today)
		{
			// No end date so this must be rate update
			// which is only allowed if the current start date is in the future
			$errors[] = 'Cannot change rate - current start date before today';
		}
		elseif (!empty($data['end_date']) && $end_date < $today)
		{
			$errors[] = 'Cannot set end date before today';
		}
		else
		{
			// Just update the current with the change of rate or end date
			parent::save($this->modeltype, $this->_data, $errors);
		}

		if (count($errors)>0)
		{
			$db->FailTrans();
		}

		$db->CompleteTrans();
	}

	private function validate_new($data, $employee, &$errors = array())
	{
		// TODO: Move to hr_controller as parent; see also EmployeecontractdetailsController

		// Check that the new rate does not overlap an existing rate of this type
		// Standard rate start date must either match the employee start date
		// if this is only rate for the employee, or must be one day greater
		// than an existing rate
		$existing = DataObjectFactory::Factory($this->modeltype);

		$existing->getLatest($data['employee_id'], $data['payment_type_id']);

		$new_start_date = fix_date($data['start_date']);
		$set_end_date = fix_date(date(DATE_FORMAT, strtotime($new_start_date.' -1 day')));

		$db = DB::Instance();
		$db->StartTrans();

		if ($existing->isLoaded())
		{
			// Rules :
			// Input start date must be after existing start date
			if ($existing->start_date >= $new_start_date)
			{
				$errors[] = 'Start date must be after latest rate start';
			}
			elseif (is_null($existing->end_date))
			{
				// Close off existing end date as day before new start date
				$existing->end_date = $set_end_date;

				if (!$existing->save())
				{
					$errors[] = 'Error closing off latest entry : '.$db->ErrorMsg();
				}

			}
		}

		if (count($errors == 0))
		{
			$new = DataObject::Factory($data, $errors, $this->modeltype);

			if ($new && !$new->save())
			{
				$errors[] = 'Error saving new details : '.$db->ErrorMsg();
			}
		}


		if (count($errors) > 0)
		{
			$db->FailTrans();
		}

		$db->CompleteTrans();

	}
}

// End of EmployeeratesController
