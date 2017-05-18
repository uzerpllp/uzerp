{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="engineeringresources" action="save"}
		{with model=$models.EngineeringResource legend="Engineering Resource Details"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{if !is_null($model->work_schedule_id)}
				{view_data model=$workschedule attribute='job_no'}
				{view_data model=$workschedule attribute='description'}
				{view_data model=$workschedule attribute='centre'}
			{/if}
			{select attribute='work_schedule_id' value=$workschedules}
			{select attribute='resource_id' }
			{select attribute='person_id' options=$people}
			{input attribute='quantity' }
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	{include file="elements/datatable.tpl" collection=$engineeringresources}
{/content_wrapper}