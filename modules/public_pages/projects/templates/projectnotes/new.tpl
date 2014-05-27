{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="projectnotes" action="save"}
		{with model=$models.ProjectNote}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='usercompanyid' }
			{input type='hidden' attribute='project_id' }
			{input type='text' attribute='title' }
			{select attribute='type_id' }
			{textarea attribute='note' class="compulsory"}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}