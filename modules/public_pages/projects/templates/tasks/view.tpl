{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		{with model=$Task}
			{view_section heading="Task Details"}
				{view_data attribute="name"}
				{view_data attribute="project" link_to='"module":"projects", "controller":"projects", "action":"view", "id":"'|cat:$model->project_id|cat:'"'}
				{view_data attribute="parent" link_to='"module":"projects", "controller":"tasks", "action":"view", "id":"'|cat:$model->parent_id|cat:'"'}
				{view_data attribute="owner"}
				{view_data attribute="milestone"}
				{view_data attribute="deliverable"}
			{/view_section}
			{view_section heading="Timescale"}
				{view_data attribute="start_date"}
				{view_data attribute="end_date"}
				{view_data attribute="duration" modifier="to_working_days"}
			{/view_section}
		{/with}
		</dl>
		<dl id="view_data_right">
		{with model=$Task}
			{view_section heading="Task Status"}
				{view_data attribute="priority"}
				{view_data attribute="progress"}
			{/view_section}
			{view_section heading="Access Details"}
				{view_data attribute="created"}
				{view_data attribute="lastupdated"}
			{/view_section}
		{/with}
		</dl>
		<div id="view_data_fullwidth">
			{with model=$Task}
				{view_data attribute="description"}
			{/with}
		</div>
	</div>
{/content_wrapper}