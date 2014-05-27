{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.5 $ *}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{if isset($datamapping)}
				{view_data model=$datamapping attribute="external_system"}
				{view_data model=$datamapping attribute="name" label='Mapping Rule'}
			{/if}
			{if isset($parent)}
				{view_data model=$parentmodel attribute="parent_type" value=$parent_type}
				{view_data model=$parentmodel attribute="value" value=$parent}
			{/if}
		</dl>
		{paging}
		{data_table}
			{heading_row}
				{heading_cell field="external_code"}
					External Value
				{/heading_cell}
				{heading_cell field="internal_code"}
					Internal Value
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$datamappingdetails}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="external_code"}
						{$model->external_code}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="internal_code"}
						{$model->displayValue()}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
	</div>
{/content_wrapper}