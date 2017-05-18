{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="campaigns" action="save"}
		{with model=$models.Campaign legend="Campaign Details"}
			{view_section heading="activity_details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='text'  attribute='name' class="compulsory" }
				{input type='date'  attribute='startdate' }
				{input type='date'  attribute='enddate' }
				{input type='text'  attribute='actual_cost' }
				{input type='checkbox'  attribute='active' }
				{input type='text'  attribute='number_sent' }
				{input type='text'  attribute='budget' }
				{input type='text'  attribute='expected_cost' }
				{input type='text'  attribute='expected_revenue' }
				{input type='text'  attribute='actual_revenue' }
				{input type='text'  attribute='expected_response' }
				{input type='text'  attribute='actual_response' }
				{input type='text'  attribute='target_audience' }
				{select attribute='campaign_type_id' }
				{select attribute='campaign_status_id' }
				{textarea  attribute='description' }
				{textarea attribute='objective' }
				{submit}
			{/view_section}
		{/with}
	{/form}
{/content_wrapper}