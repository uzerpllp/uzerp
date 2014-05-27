{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="newsletters" action="save"}
		{with model=$models.Newsletter legend="Newsletter Details"}
			{view_section heading="activity_details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='text'  attribute='name'}
				{input type='text'  attribute='newsletter_url' }
				{datetime  attribute='send_at' }
				{select attribute='campaign_id' }
				{submit}
			{/view_section}
		{/with}
	{/form}
{/content_wrapper}