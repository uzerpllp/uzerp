{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.7 $ *}
	<div id="{page_identifier}">
		{form controller="datadefinitiondetails" action="save"}
			{with model=$models.DataDefinitionDetail legend="Data Definitions"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{select  attribute='data_definition_id' class="compulsory" }
				{select  attribute='parent_id' class="compulsory" options=$parent}
				{input type='text'  attribute='element' class="compulsory" }
				{input type='text'  attribute='position' class="compulsory" }
				{select  attribute='data_mapping_id' class="compulsory" options=$mapping}
				{select  attribute='data_mapping_rule_id' class="compulsory" options=$rules}
				{input type='text'  attribute='default_value'}
			{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}