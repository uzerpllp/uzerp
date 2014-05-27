{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="workschedulenotes" action="save"}
		{with model=$models.WorkScheduleNote legend="Work Schedule Note"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='work_schedule_id' }
			{include file='elements/auditfields.tpl'}
			{input type='text'  attribute='title'}
			{textarea attribute='note'}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}