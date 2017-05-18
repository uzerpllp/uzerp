{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="paramdesc" class="wide_column"}
				Description
			{/heading_cell}
			{heading_cell field="display_value"}
				Value
			{/heading_cell}
			{heading_cell field="lastupdated"}
				Last Updated
			{/heading_cell}
			{heading_cell field="alteredby"}
				Updated By
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$glparamss}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=1 field="paramdesc"}
					{$model->paramdesc}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="display_value"}
					{$model->display_value}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="lastupdated"}
					{$model->lastupdated|un_fix_date:true}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="alteredby"}
					{$model->alteredby}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
	{paging}
{/content_wrapper}