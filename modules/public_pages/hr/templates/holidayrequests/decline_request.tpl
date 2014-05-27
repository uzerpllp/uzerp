{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper title=$page_title}
	{form controller="holidayrequests" action="save"}
		{with model=$Holidayrequest legend="Holiday Request Details"}
			{view_section heading="request_details"}
				{input type='hidden' attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='hidden' attribute='employee_id'}
				{input type='hidden' attribute='num_days'}
				{input type='hidden' attribute='approved_by' value=$authoriser}
				{input type='hidden' attribute='status'}
				{view_data attribute='employee'}
				{view_data attribute='start_date'}
				{view_data attribute='end_date'}
				{view_data attribute='num_days' label='Number of Days'}
				{view_data attribute='employee_notes'}
				{view_data attribute='special_circumstances' }
				{view_data attribute='current_status' value=$model->getFormatted('status')}
			{/view_section}
			{textarea attribute='reason_declined' notags=true}
			{submit}
		{/with}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}