{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.4 $ *}
	<div id="{page_identifier}">
		{form controller="externalsystems" action="save"}
			{with model=$models.ExternalSystem legend="External Systems"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{input type='text'  attribute='name' class="compulsory" }
				{input type='textarea'  attribute='description' class="compulsory" }
			{/with}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}