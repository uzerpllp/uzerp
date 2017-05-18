{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller="calendarevents" action="batchcomplete"}
		{data_table}
			{heading_row}
				{heading_cell field="title"}
					Title
				{/heading_cell}
				{heading_cell field="calendar"}
					Calendar
				{/heading_cell}
				{heading_cell field="start_time"}
					Start
				{/heading_cell}
				{heading_cell field="end_time"}
					End
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$calendarevents}
				<tr>
					<td>{link_to module="calendar" controller="calendarevents" action="view" id=$model->id value=$model->title}</td>
					<td>{$model->calendar}</td>
					<td>{$model->start_time|date_format:"%d/%m/%Y %H:%M"}</td>
					<td>{$model->end_time|date_format:"%d/%m/%Y %H:%M"}</td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{if $num_incomplete > 0}
			{submit value="Update Selected" tags="none"}
		{/if}
	{/form}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}