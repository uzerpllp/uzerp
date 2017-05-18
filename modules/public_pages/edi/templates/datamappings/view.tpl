{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{* $Revision: 1.6 $ *}
	<div id="view_page" class="clearfix">
		{with model=$mapping}
			{view_data attribute="name"}
			{view_data attribute="parent"}
			{view_data attribute="internal_type"}
			{view_data attribute="internal_attribute"}
		{/with}
		<br>
		{view_section heading='Used By'}
			{data_table}
				{heading_row}
					{heading_cell field="mapping_rule"}
						Mapping Rule
					{/heading_cell}
					{heading_cell field="data_definition"}
						Data Definition
					{/heading_cell}
					{heading_cell field="parent"}
						Parent
					{/heading_cell}
					{heading_cell field="element"}
						Element
					{/heading_cell}
				{/heading_row}
				{foreach name=datagrid item=model from=$datadefinitiondetails}
					{grid_row model=$model}
						{grid_cell model=$model cell_num=1 field="mapping_rule"}
							{$model->mapping_rule}
						{/grid_cell}
						<td>
							{link_to module=$module controller=datadefinitiondetails action='viewdatadefinition' data_definition_id=$model->data_definition_id value=$model->data_definition}
						</td>
						{grid_cell model=$model cell_num=3 field="parent"}
							{$model->parent}
						{/grid_cell}
						{grid_cell model=$model cell_num=3 field="element"}
							{$model->element}
						{/grid_cell}
					{/grid_row}
				{foreachelse}
					<tr><td colspan="0">No matching records found!</td></tr>
				{/foreach}
			{/data_table}
		{/view_section}
	</div>
{/content_wrapper}