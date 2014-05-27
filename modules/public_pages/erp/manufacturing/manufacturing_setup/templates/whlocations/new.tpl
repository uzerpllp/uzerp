{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="whlocations" action="save"}
		{with model=$models.WHLocation legend="WHLocation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='location' class="compulsory" }
			{input type='text'  attribute='description' }
			{input type='checkbox'  attribute='has_balance' }
			{input type='checkbox'  attribute='supply_demand' }
			{input type='checkbox'  attribute='bin_controlled' }
			{input type='checkbox'  attribute='saleable' }
			{input type='checkbox'  attribute='pickable' }
			{input type='hidden'  attribute='whstore_id' }
			{select attribute='glaccount_id' options=$accounts label='GL Account' nonone=true}
			{select attribute='glcentre_id' options=$centres label='Cost Centre' nonone=true}
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}