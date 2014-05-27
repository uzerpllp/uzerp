{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{advanced_search}
	{paging}
	{data_table}
		{heading_row}
			{heading_cell field="transaction_date"}
				Transaction Date
			{/heading_cell}
			{heading_cell field="transaction_type"}
				Transaction Type
			{/heading_cell}
			{heading_cell field="armaster"}
				Asset Code
			{/heading_cell}
			{heading_cell field="from_group"}
				From Group
			{/heading_cell}
			{heading_cell field="from_location"}
				From Location
			{/heading_cell}
			{heading_cell field="to_group"}
				To Group
			{/heading_cell}
			{heading_cell field="to_location"}
				To Location
			{/heading_cell}
			{heading_cell field="value" class='right'}
				Value
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$artransactions}
			{grid_row model=$model}
				<td>
					{link_to module='general_ledger' controller='gltransactions' action='index' docref='T'|cat:$model->id source='A' value=$model->transaction_date}
				</td>
				{grid_cell model=$model cell_num=2 field="transaction_type"}
					{$model->getFormatted('transaction_type')}
				{/grid_cell}
				<td>
					{link_to module=$module controller='assets' action='view' id=$model->armaster_id value=$model->armaster}
				</td>
				<td>
					{link_to module=$module controller='argroups' action='view' id=$model->from_group_id value=$model->from_group}
				</td>
				<td>
					{link_to module=$module controller='arlocations' action='view' id=$model->from_location_id value=$model->from_location}
				</td>
				<td>
					{link_to module=$module controller='argroups' action='view' id=$model->to_group_id value=$model->to_group}
				</td>
				<td>
					{link_to module=$module controller='arlocations' action='view' id=$model->to_location_id value=$model->to_location}
				</td>
				{grid_cell model=$model cell_num=6 field="value"}
					{$model->value}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="description"}
					{$model->description}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr>
				<td colspan="0">No matching records found!</td>
			</tr>
		{/foreach}
	{/data_table}
{/content_wrapper}