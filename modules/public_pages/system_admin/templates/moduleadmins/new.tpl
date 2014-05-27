{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="moduleadmins" action="save"}
		{with model=$models.ModuleAdmin legend="ModuleAdmin Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{select  attribute='role_id' class="compulsory" }
			{input type='text'  attribute='module_name' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}