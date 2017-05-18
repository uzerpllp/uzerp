{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form action="user"}
		{with model=$models.User legend="User Details"}
			{input type='text'  attribute='username' class="compulsory" }
			{input type='text'  attribute='password' class="compulsory" }
			{input type='text'  attribute='lastcompanylogin' class="numeric" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}