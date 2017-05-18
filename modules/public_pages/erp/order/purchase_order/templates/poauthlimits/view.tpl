{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.2 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$model}
			<dl id="view_data_left">
				{assign var='title' value=$model->getIdentifierValue()}
				{view_section heading="$title"}
					{assign var=fields value=$model->getDisplayFieldNames()}
					{foreach name=gridrow item=tag key=fieldname from=$fields}
						{view_data attribute=$fieldname label=$tag}
					{/foreach}
				{/view_section}
			</dl>
		{/with}
	</div>
	{data_table}
		{heading_row}
			{heading_cell field="glaccount"}
				GL Account
			{/heading_cell}
		{/heading_row}
		{foreach item=authaccount from=$authaccounts}
			{grid_row}
				<td>
					{$authaccount}
				</td>
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}