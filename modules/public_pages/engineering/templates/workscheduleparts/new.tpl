{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="workscheduleparts" action="save"}
		{with model=$models.WorkSchedulePart legend="Work Schedule Part"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl'}
			{if isset($workschedule)}
				{view_data model=$workschedule attribute=job_no}
				{view_data model=$workschedule attribute="description"}
				{view_data model=$workschedule attribute="start_date"}
				{view_data model=$workschedule attribute="end_date" modifier="overdue"}
				{view_data model=$workschedule attribute="status"}
				{view_data model=$workschedule attribute="centre_id"}
			{/if}
			{select attribute='work_schedule_id'}
			{select attribute='productline_header_id' options=$products}
			{input attribute='order_qty'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}