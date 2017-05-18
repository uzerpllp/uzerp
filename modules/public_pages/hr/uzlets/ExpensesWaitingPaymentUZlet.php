<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ExpensesWaitingPaymentUZlet extends SimpleListUZlet
{

	protected $version = '$Revision: 1.2 $';
	
	function getClassName()
	{
		return 'eglet double_eglet';
	}
	
	function populate()
	{
		$employee = DataObjectFactory::Factory('Employee');		
		
		$user = getCurrentUser();
		
		if (!is_null($user->person_id))
		{
			$employee->loadBy('person_id', $user->person_id);
		}
		
		if ($employee->isLoaded())
		{
			$authorisor_model = $employee->expense_model();
			
			$employee->authorisationPolicy($authorisor_model);
			
			$authorisees = $employee->getAuthorisees($authorisor_model);
		}
		else
		{
			$authorisees = array();
		}
		
		$expense = DataObjectFactory::Factory('Expense');		
		
		$expenses = new ExpenseCollection($expense);
		
		if (count($authorisees) > 0)
		{
			$expenses->setParams();
			
			$sh = new SearchHandler($expenses,false);
			
			$sh->setFields(array('id', 'expense_number', 'employee', 'employee_id', 'description', 'gross_value'));
			
			$sh->addConstraint(new Constraint('status', '=', $expense->statusAwaitingPayment()));
			$sh->addConstraint(new Constraint('employee_id', 'in', '(' . implode(',', $authorisees) . ')'));
			
			$this->setSearchLimit($sh);
			
			$sh->setOrderby(array('expense_number'));
			
			$expenses->load($sh);
			
			$expenses->clickcontroller = 'expenses';
			$expenses->editclickaction = 'view';
		}
		
		$this->contents = $expenses;
	}
	
}

// End of ExpensesWaitingPaymentUZlet
