{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$mappingrule}
			{view_data attribute="external_system"}
			{view_data attribute="name" label='Mapping Rule'}
			{view_data attribute="parent_rule"}
			{view_data attribute="external_format"}
			{view_data attribute="data_type"}
		{/with}
		<br>
		{view_section heading='Child Rules'}
			{paging}
			{data_table}
				{heading_row}
					{heading_cell field="name"}
						Name
					{/heading_cell}
				{/heading_row}
				{foreach name=datagrid item=model from=$datamappingrules}
					{grid_row model=$model}
						{grid_cell model=$model cell_num=1 field="name"}
							{$model->name}
						{/grid_cell}
					{/grid_row}
				{foreachelse}
					<tr>
						<td colspan="0">No matching records found!</td>
					</tr>
				{/foreach}
			{/data_table}
			{paging}
		{/view_section}
	</div>
{/content_wrapper}