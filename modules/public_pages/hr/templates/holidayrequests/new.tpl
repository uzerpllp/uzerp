{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.10 $ *}
{content_wrapper title=$page_title|cat:' for '|cat:$employee->person->getIdentifierValue()}
	{form controller="holidayrequests" action="save"}
		{with model=$models.Holidayrequest legend="Holiday Request Details"}
			{view_section heading="request_details"}
				{input type='hidden' attribute='id' }
				{include file='elements/auditfields.tpl' }
				{select attribute='employee_id' value=$employee->id options=$employees}
				{view_data attribute=days_left value=$days_left label='Days Left'}
				{input type='hidden' attribute='today' value=$today}
				{input type='date' attribute='start_date'}
				{input type='date' attribute='end_date'}
				{input type='radio' attribute='all_day' rowid=1 value='t' label='Full Day'}
				{input type='radio' attribute='all_day' rowid=2 value='f' label='Half Day'}
				{input type='text' attribute='num_days' value=$model->num_days class="compulsory" label="Number of Days"}
				{view_data attribute=new_days_left value=$new_days_left label='New Days Left'}
				{input type='checkbox'  attribute='special_circumstances' }
			{/view_section}
			{textarea attribute="employee_notes"}
			{submit}
			{include file='elements/saveAnother.tpl'}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}