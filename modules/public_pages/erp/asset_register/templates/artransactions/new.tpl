{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="artransactions" action="save"}
		{with model=$models.ARTransaction legend="Asset Transaction Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='description' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}