{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="leads" action="converttoaccount"}
		{with model=$models.Lead legend="Lead Details"}
			{input type='hidden' attribute='id'}
			{input type='text' attribute='accountnumber' label='Account Number'}
		{/with}
		{submit}
	{/form}
{/content_wrapper}