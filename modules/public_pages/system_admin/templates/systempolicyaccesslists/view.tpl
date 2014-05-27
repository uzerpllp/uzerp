{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$SystemPolicyAccessList}
			<dl id="view_data_left">
				{view_data attribute="access_type"}
				{view_data attribute="name" value=$model->getAccessValue()}
			</dl>
		{/with}
	</div>
	<div id="view_page" class="clearfix">
		{view_section heading='Policy_Control_lists'}
		{/view_section}
		{assign var=fields value=$systempolicycontrollists->getHeadings()}
		{data_table}
			{heading_row}
				{with model=$systempolicycontrollists->getModel()}
					{heading_cell field='allowed' model=$model}
						Access
					{/heading_cell}
					{heading_cell field='policy' model=$model}
						Policy
					{/heading_cell}
					{heading_cell field='condition' model=$model}
						Condition
					{/heading_cell}
					{heading_cell field='condition' model=$model}
						Type
					{/heading_cell}
				{/with}
			{/heading_row}
			{foreach name=datagrid item=model from=$systempolicycontrollists}
				{grid_row model=$model}
					<td>
						{if $model->allowed=='t'}
							{assign var=permission value='Allow'}
						{else}
							{assign var=permission value='Deny'}
						{/if}
						{link_to module=$module controller='systempolicycontrollists' action='edit' id=$model->id value=$permission}
					</td>
					{grid_cell field=$fieldname cell_num=2 model=$model field='policy' no_escape=true}
						{$model->policy}
					{/grid_cell}
					{grid_cell field=$fieldname cell_num=2 model=$model field='condition'}
						{$model->getObjectPolicyValue()}
					{/grid_cell}
					{grid_cell field=$fieldname cell_num=2 model=$model field='condition'}
						{$model->type}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
	</div>
{/content_wrapper}