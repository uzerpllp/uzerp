{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="companypermissions" action="save"}
		{with model=$models.CompanyPermission legend="CompanyPermission Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}