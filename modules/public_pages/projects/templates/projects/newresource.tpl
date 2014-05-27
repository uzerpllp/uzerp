{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="projects" action="saveresource"}
		{with model=$models.Resource legend="Resource Details"}
			<dl id="view_data_left">
			{view_section heading="resource_details"}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='usercompanyid' }
				{input type='hidden'  attribute='project_id' }
				{select attribute='person_id' }
				{select attribute='resource_type_id'}
				{input type='checkbox'  attribute='project_manager'}
			{/view_section}
			{view_section heading="resource_costs"}
				{input type='text'  attribute='standard_rate'}
				{input type='text'  attribute='overtime_rate'}
				{input type='text'  attribute='quantity'}
				{if isModuleAdmin()}
					{input type='text'  attribute='cost' label='unit_cost'}
				{/if}
			{/view_section}
			{submit another='false'}
			{view_section heading="resource_template"}
				<dt>Resource template:</dt>
				<dd>
					<select name="resource_templates">
						{html_options options=$resourceTemplates}
					</select>
				</dd>
				<dt class='submit'></dt><dd class="submit"><input type="submit" name="add_resource_template" value="Add From Template"/></dd>
			{/view_section}
			{view_section heading="remove_from_project"}
				<dt class="submit"></dt><dd class="submit"><input type="submit" name="remove_from_project" value="Remove" /></dd>
			{/view_section}
			</dl>
		{/with}
	{/form}
{/content_wrapper}