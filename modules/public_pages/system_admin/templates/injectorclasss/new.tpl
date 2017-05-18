{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="injectorclasss" action="save"}
		<dl id="view_data_left">
			{with model=$models.InjectorClass legend="Injector Class Details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='text'  attribute='name' class="compulsory" }
				{input type='text'  attribute='class_name' class="compulsory" }
				{select attribute='category' class="compulsory" }
				{textarea attribute='description' }
			{/with}
		{submit}
	{/form}
{/content_wrapper}