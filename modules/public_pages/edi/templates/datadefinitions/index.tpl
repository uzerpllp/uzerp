{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{if !$external_system_id}
				{heading_cell field="external_system"}
					External System
				{/heading_cell}
			{/if}
			{heading_cell field="name"}
				Name
			{/heading_cell}
			{heading_cell field="type"}
				Type
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{heading_cell field="direction"}
				Direction
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$datadefinitions}
			{grid_row model=$model}
				{if !$external_system_id}
					{grid_cell model=$model cell_num=5 field="external_system"}
						{$model->external_system}
					{/grid_cell}
				{/if}
				{grid_cell model=$model cell_num=1 field="name" _implementation_class=$model->implementation_class}
					{$model->name}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="type"}
					{$model->type}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="description"}
					{$model->description}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="direction"}
					{$model->direction}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
{/content_wrapper}