{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	{form controller="glperiods" action="save"}
		{with model=$models.GLPeriod legend="Periods Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='year' class="compulsory" }
			{input type='text'  attribute='period' class="compulsory" }
			{input type='text'  attribute='description' class="compulsory" }
			{input type='date'  attribute='enddate' class="compulsory" }
			{input type='text'  attribute='tax_period' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}