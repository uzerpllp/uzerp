{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	{form controller="resources" action="save"}
		{with model=$models.Resource legend="ProjectResource Details"}
			{input type='hidden' attribute='id' }
			{include file='elements/auditfields.tpl' }
			{select attribute='project_id' force=true value=$project_id}
			{select attribute='task_id' options=$tasks depends='project_id'}
			{select attribute='resource_id' }
			{select attribute='person_id' options=$people}
			{input type=date attribute='start_date' value=$start_date}
			{input type=date attribute='end_date' value=$end_date}
			{input attribute='quantity' }
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}