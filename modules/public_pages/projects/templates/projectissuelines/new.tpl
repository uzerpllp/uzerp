{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="projectissuelines" action="save"}
		{with model=$models.ProjectIssueLine}
			{include file='elements/auditfields.tpl' }
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='header_id' }
			{input type='text' attribute='title' }
			{input type='text' attribute='location' }
			{textarea attribute="description"}
			{textarea attribute="actions"}
			{input type='date' attribute="created"}
			{input type='date' attribute="completed"}
			{select attribute='completed_by'}
		{/with}
		{submit}
	{/form}
{/content_wrapper}