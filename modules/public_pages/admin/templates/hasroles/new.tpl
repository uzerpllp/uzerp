{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="hasroles" action="save"}
		{with model=$models.HasRole legend="HasRole Details"}
			{input type='hidden'  attribute='id' }
			{select attribute='roles_roleid' }
			{select attribute='users_username' }
		{/with}
		{submit}
	{/form}
{/content_wrapper}