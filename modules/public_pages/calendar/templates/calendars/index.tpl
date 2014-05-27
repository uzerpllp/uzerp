{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{form controller="calendars"}
		{data_table}
			{heading_row}
				{heading_cell}
					Name
				{/heading_cell}
				{heading_cell}
					Type
				{/heading_cell}
				{heading_cell}
					Owner
				{/heading_cell}
				{heading_cell}
					Colour
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$calendar}
				<tr>
					<td>{link_to module="calendar" controller="calendars" action="edit_"|cat:$model->type id=$model->id value=$model->name}</td>
					<td>{$model->getFormatted('type')}</td>
					<td>{$model->owner}</td>
					<td><p style="width: 30px; background-color:{$model->colour};">&nbsp</p></td>
				</tr>
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
	{/form}
	<div id="data_grid_footer" class="clearfix">
		{paging}
		{include file='elements/data_table_actions.tpl'}
	</div>
{/content_wrapper}