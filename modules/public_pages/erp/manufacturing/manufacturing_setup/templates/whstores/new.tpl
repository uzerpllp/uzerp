{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="whstores" action="save"}
		{with model=$models.WHStore legend="WHStore Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='store_code' class="compulsory" }
			{input type='text'  attribute='description' }
			{select attribute='address_id' options=$addresses label='Address'}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}