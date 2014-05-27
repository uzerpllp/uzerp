{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.6 $ *}
	<div id="{page_identifier}">
		{form controller="datamappings" action="save"}
			{with model=$models.DataMapping legend="Data Mapping"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{select attribute='parent_id'}
				{input type='text'  attribute='name' class="compulsory" }
				{input type='text'  attribute='internal_type' class="compulsory" }
				{input type='text'  attribute='internal_attribute' class="compulsory" }
			{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}