{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="workschedules" action="save"}
		{with model=$models.WorkSchedule legend="Work Schedule Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl'}
			{textarea attribute='description'}
			{input type='date' attribute='start_date'}
			{input type='date' attribute='end_date'}
			{select attribute='centre_id'}
			{input type='text' attribute='planned_time'}
			{input type='text' attribute='actual_time'}
			{select attribute='mf_downtime_code_id' label='Downtime Code'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}