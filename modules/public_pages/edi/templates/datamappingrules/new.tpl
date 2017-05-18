{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.5 $ *}
	<div id="{page_identifier}">
		{form controller="datamappingrules" action="save"}
			{with model=$models.DataMappingRule legend="Data Mapping"}
				{input type='hidden'  attribute='id' }
				{include file='elements/auditfields.tpl' }
				{select attribute='data_mapping_id' class="compulsory" }
				{select attribute='external_system_id' class="compulsory" }
				{select attribute='parent_id' class="compulsory" options=$parent}
				{input attribute='name' class="compulsory" }
				{input attribute='external_format'}
				{input attribute='data_type'}
			{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}