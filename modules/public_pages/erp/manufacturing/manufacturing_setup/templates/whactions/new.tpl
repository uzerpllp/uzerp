{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="whactions" action="save"}
		{with model=$models.WHAction legend="WHAction Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='action_name' class="compulsory" }
			{input type='text'  attribute='description' }
			{input type='text'  attribute='label' }
			{input type='text'  attribute='position' }
			{input type='text'  attribute='max_rules' }
			{select attribute='from_has_balance' }
			{select attribute='from_bin_controlled' }
			{select attribute='from_saleable' }
			{select attribute='to_has_balance' }
			{select attribute='to_bin_controlled' }
			{select attribute='to_saleable' }
			{select attribute='type'}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}