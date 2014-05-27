{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="resourcetemplate" action="save"}
		{with model=$models.Resourcetemplate legend="Resource Details"}
			<dl id="view_data_left">
			{view_section heading="Resource_details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='hidden'  attribute='project_id' }
				{select attribute='person_id' }
				{input type='text' attribute='name'}
				{select attribute='resource_type_id'}
			{/view_section}
			{view_section heading="Resource_costs"}
				{input type='text'  attribute='standard_rate'}
				{input type='text'  attribute='overtime_rate'}
				{input type='text'  attribute='quantity'}
				{input type='text'  attribute='cost' label='unit_cost'}
			{/view_section}
			{submit}
			</dl>
		{/with}
	{/form}
{/content_wrapper}