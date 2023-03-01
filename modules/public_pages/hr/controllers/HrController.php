<?php

class HrController extends printController
{
	public function __construct($module = null, $action = null) {

	    parent::__construct($module, $action);

	    $policy = DataObjectFactory::Factory('SystemObjectPolicy');
	    if ($policy->getCount() == 0)
	    {
	        $flash = Flash::Instance();
			$flash->addError('HR Expenses and Holiday Requests require System Policies to be defined.');
	    }
	}

	/*
	 * Get the employee id for the current user
	 */
	protected function get_employee_id()
	{

		$user = getCurrentUser();

		if ($user && !is_null($user->person_id))
		{
			$employee = DataObjectFactory::Factory('Employee');

			$employee->loadBy('person_id', $user->person_id);

			if ($employee->isLoaded())
			{
				return $employee->id;
			}
		}

		// User is not an employee
		return '';
	}

}

// End of HrController
