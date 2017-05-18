{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="whtransferrules" action="save"}
		{with model=$models.WHTransferrule legend="WHTransferrule Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='hidden'  attribute='whaction_id' }
			{select attribute='from_whlocation_id' options=$from_location label='From Location'}
			{select attribute='to_whlocation_id' options=$to_location label='To Location'}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}