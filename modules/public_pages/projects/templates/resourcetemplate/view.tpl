{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$Resourcetemplate}
		<dt class="heading">Resource details</dt>
			{view_data attribute="person"}
			{view_data attribute="name"}
		
		<dt class="heading">Resource Costs
			{view_data attribute="standard_rate"}
			{view_data attribute="overtime_rate"}
			{view_data attribute="quantity"}
			{view_data attribute="cost" label="unit_cost"}
		{/with}
		</dl>
	</div>
{/content_wrapper}