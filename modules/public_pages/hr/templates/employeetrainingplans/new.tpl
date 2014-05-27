{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="employeetrainingplans" action="save"}
		{with model=$models.EmployeeTrainingPlan legend="Employee Training Plan"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='employee_id' }
			{include file='elements/auditfields.tpl' }
			{select  attribute='training_objective_id' }
			{input type='date'  attribute='expected_start_date'}
			{input type='date'  attribute='expected_end_date'}
			{input type='date'  attribute='actual_start_date'}
			{input type='date'  attribute='actual_end_date'}
			{input type='text'  attribute='progress' }
			{textarea attribute='description' }
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}