{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="activitynotes" action="save"}
		{with model=$models.ActivityNote legend="ActivityNote Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='text'  attribute='title' class="compulsory" }
			{select attribute='activity_id' }
			{textarea attribute='note' class="compulsory" }
		{/with}
		{submit}
	{/form}
{/content_wrapper}