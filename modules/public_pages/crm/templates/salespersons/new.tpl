{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="salespersons" action="save"}
		{with model=$models.SalesPerson legend="SalesPerson Details"}
			{input type='hidden'  attribute='id' }
			{select attribute='person_id' }
			{input type='text'  attribute='base_commission_rate' class="numeric compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}