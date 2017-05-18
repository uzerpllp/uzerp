{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$Task}
			<dt class="heading">Task Details</dt>
				{view_data attribute="name"}
				{view_data attribute="project"}
				{view_data attribute="parent_task"}
				{view_data attribute="milestone"}
			<dt class="heading">Timescale</dt>
				{view_data attribute="start_date"}
				{view_data attribute="end_date"}
		{/with}
		</dl>
		<dl id="view_data_right">
		{with model=$Task}
			<dt class="heading">Task Status</dt>
				{view_data attribute="budget"}
				{view_data attribute="priority"}
				{view_data attribute="progress" type="percentage"}
			<dt class="heading">Access Details</dt>
				{view_data attribute="created"}
				{view_data attribute="lastupdated"}
		{/with}
		</dl>
		<div id="view_data_fullwidth">
			{with model=$Task}
				{view_data attribute="description"}
			{/with}
		</div>
	</div>
{/content_wrapper}