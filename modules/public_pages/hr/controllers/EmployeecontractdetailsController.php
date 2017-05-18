<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class EmployeecontractdetailsController extends Controller
{

	protected $version = '$Revision: 1.2 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null)
	{
		
		parent::__construct($module, $action);
		
		$this->_templateobject = DataObjectFactory::Factory('EmployeeContractDetail');
		
		$this->uses($this->_templateobject);
	}
	
	/*
	 * Standard index listing of Employee Contract Details
	 */
	public function index()
	{
		
		$this->view->set('clickaction', 'edit');
		
		parent::index(new EmployeeContractDetailCollection($this->_templateobject));

	}	
	
	/*
	 * Delete of Employee Contract Details is not allowed
	 */
	public function delete()
	{
		sendBack();
	}
	
	/*
	 * Enter a new Employee Contract Detail
	 */
	public function _new()
	{
		
		$flash = Flash::Instance();
		
		parent::_new();
		
		$ee_contract_detail = $this->_uses[$this->modeltype];
		
		if ($ee_contract_detail->isLoaded())
		{
			$this->_data['employee_id'] = $ee_contract_detail->employee_id;
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
		
		$collection = new EmployeeContractDetailCollection($this->_templateobject);
		
		$sh = $this->setSearchHandler($collection);
		
		$sh->addConstraint(new Constraint('employee_id', '=', $this->_data['employee_id']));
		
		$this->view->set('clickaction', 'view');
		
		parent::index($collection, $sh);
		
		if (!$ee_contract_detail->isLoaded())
		{
			
			if ($collection->count() > 0)
			{
				$latest = $collection->current();
				
				if (!is_null($latest->end_date))
				{
					$ee_contract_detail->start_date = date(DATE_FORMAT, strtotime($latest->end_date . ' + 1 day'));
				}
				elseif ($start_date > fix_date(date(DATE_FORMAT)))
				{
					$ee_contract_detail->start_date = date(DATE_FORMAT, strtotime($latest->start_date . ' + 1 day'));
				}
				else
				{
					$ee_contract_detail->start_date = fix_date(date(DATE_FORMAT));
				}
			}
			else
			{
				$ee_contract_detail->start_date = $employee->start_date;
			}
			
			$ee_contract_detail->end_date		= $employee->finished_date;
		}
		
		if (!is_null($employee->finished_date) && $employee->finished_date < $start_date)
		{
			$flash->addError('Employee is leaving');
			sendBack();
		}
		
	}
	
	/*
	 * Save the Employee Contract Detail
	 */
	public function save()
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
				  ,isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
		}
		
				
		$flash = Flash::Instance();
		$flash->addErrors($errors);
		
		$this->refresh();

	}
	
	/*
	 * Private Functions
	 */
	private function validate_edit($current, $data, $employee, &$errors = array())
	{
		// TODO: Move to hr_controller as parent; see also EmployeeratesController
		
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
		// TODO: Move to hr_controller as parent; see also EmployeeratesController
		
		// Check that the new rate does not overlap an existing rate of this type
		// Standard rate start date must either match the employee start date
		// if this is only rate for the employee, or must be one day greater
		// than an existing rate
		$existing = DataObjectFactory::Factory($this->modeltype);
		
		$existing->getLatest($data['employee_id'], $data['from_pay_frequency_id'], $data['to_pay_frequency_id']);
		
		$new_start_date = fix_date($data['start_date']);
		$set_end_date	= fix_date(date(DATE_FORMAT, strtotime($new_start_date.' -1 day')));
		
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

// End of EmployeecontractdetailsController
