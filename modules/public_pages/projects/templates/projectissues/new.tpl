{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="projectissues" action="save"}
		{with model=$models.ProjectIssueHeader}
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='project_id' }
			{input type='text' attribute='title' }
			{select attribute='status' }
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}