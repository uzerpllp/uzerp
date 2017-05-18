{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$stitem attribute="item_code"}
			{view_data model=$stitem attribute="description"}
		</dl>
	</div>
	{advanced_search}
	{assign var=string_format value="%."|cat:$stitem->cost_decimals|cat:"f"}
	{assign var=grand_total_mat value=0}
	{assign var=grand_total_lab value=0}
	{assign var=grand_total_osc value=0}
	{assign var=grand_total_ohd value=0}
	{assign var=grand_total_cost value=0}
	<p><strong>Structures (Materials)</strong></p>
	{data_table}
		{heading_row}
			{heading_cell field="line_no"}
				Line No.
			{/heading_cell}
			{heading_cell field="ststructure"}
				Stock Item
			{/heading_cell}
			{heading_cell field="qty" class='right'}
				Quantity
			{/heading_cell}
			{heading_cell field="uom"}
				UoM
			{/heading_cell}
			{heading_cell field="waste_pc" class='right'}
				Waste %
			{/heading_cell}
			{if $type == 'std'}
				{heading_cell field="std_mat" class='right'}
					Mat
				{/heading_cell}
				{heading_cell field="std_lab" class='right'}
					Lab
				{/heading_cell}
				{heading_cell field="std_osc" class='right'}
					Osc
				{/heading_cell}
				{heading_cell field="std_ohd" class='right'}
					Ohd
				{/heading_cell}
				{heading_cell field="std_cost" class='right'}
					Total
				{/heading_cell}
			{else}
				{heading_cell field="latest_mat" class='right'}
					Mat
				{/heading_cell}
				{heading_cell field="latest_lab" class='right'}
					Lab
				{/heading_cell}
				{heading_cell field="latest_osc" class='right'}
					Osc
				{/heading_cell}
				{heading_cell field="latest_ohd" class='right'}
					Ohd
				{/heading_cell}
				{heading_cell field="latest_cost" class='right'}
					Total
				{/heading_cell}
			{/if}
		{/heading_row}
		{assign var=total_mat value=0}
		{assign var=total_lab value=0}
		{assign var=total_osc value=0}
		{assign var=total_ohd value=0}
		{foreach name=datagrid item=model from=$mfstructures}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=0 field="line_no" class='right'}
					{$model->line_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=1 field="ststructure"}
					{$model->ststructure}
				{/grid_cell}
				{grid_cell model=$model cell_num=2 field="qty" class='numeric'}
					{$model->qty}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="uom"}
					{$model->uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="waste_pc" class='numeric'}
					{$model->waste_pc}
				{/grid_cell}
				{if $type == 'std'}
					{assign var=total_mat value=$total_mat+$model->std_mat}
					{assign var=total_lab value=$total_lab+$model->std_lab}
					{assign var=total_osc value=$total_osc+$model->std_osc}
					{assign var=total_ohd value=$total_ohd+$model->std_ohd}
					{grid_cell model=$model cell_num=5 field="std_mat"}
						{$model->std_mat|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="std_lab" class='numeric'}
						{$model->std_lab|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="std_osc" class='numeric'}
						{$model->std_osc|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=8 field="std_ohd" class='numeric'}
						{$model->std_ohd|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="std_cost" class='numeric'}
						{$model->std_cost|string_format:$string_format}
					{/grid_cell}
				{else}
					{assign var=total_mat value=$total_mat+$model->latest_mat}
					{assign var=total_lab value=$total_lab+$model->latest_lab}
					{assign var=total_osc value=$total_osc+$model->latest_osc}
					{assign var=total_ohd value=$total_ohd+$model->latest_ohd}
					{grid_cell model=$model cell_num=5 field="latest_mat"}
						{$model->latest_mat|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="latest_lab" class='numeric'}
						{$model->latest_lab|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="latest_osc" class='numeric'}
						{$model->latest_osc|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=8 field="latest_ohd" class='numeric'}
						{$model->latest_ohd|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=9 field="latest_cost" class='numeric'}
						{$model->latest_cost|string_format:$string_format}
					{/grid_cell}
				{/if}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
		{if $mfstructures->count() > 0}
			{assign var=total_cost value=$total_mat+$total_lab+$total_osc+$total_ohd}
			{assign var=grand_total_mat value=$grand_total_mat+$total_mat}
			{assign var=grand_total_lab value=$grand_total_lab+$total_lab}
			{assign var=grand_total_osc value=$grand_total_osc+$total_osc}
			{assign var=grand_total_ohd value=$grand_total_ohd+$total_ohd}
			{assign var=grand_total_cost value=$grand_total_cost+$total_cost}
			<tr>
				<td colspan="5">
					<strong>Total</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_mat|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_lab|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_osc|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_ohd|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_cost|string_format:$string_format}</strong>
				</td>
			</tr>
		{/if}
	{/data_table}
	<p><strong>Operations (Labour/Overhead)</strong></p>
	{data_table}
		{heading_row}
			{heading_cell field="op_no"}
				Op No.
			{/heading_cell}
			{heading_cell field="volume_target" class='right'}
				Volume Target
			{/heading_cell}
			{heading_cell field="volume_uom"}
				UoM
			{/heading_cell}
			{heading_cell field="volume_period"}
				Volume Period
			{/heading_cell}
			{heading_cell field="quality_targt" class='right'}
				Quality Target %
			{/heading_cell}
			{heading_cell field="uptime_target" class='right'}
				Uptime Target %
			{/heading_cell}
			{if $type == 'std'}
				{heading_cell field="std_lab" class='right'}
					Lab
				{/heading_cell}
				{heading_cell field="std_ohd" class='right'}
					Ohd
				{/heading_cell}
				{heading_cell field="std_cost" class='right'}
					Total
				{/heading_cell}
			{else}
				{heading_cell field="latest_lab" class='right'}
					Lab
				{/heading_cell}
				{heading_cell field="latest_ohd" class='right'}
					Ohd
				{/heading_cell}
				{heading_cell field="latest_cost" class='right'}
					Total
				{/heading_cell}
			{/if}
		{/heading_row}
		{assign var=total_mat value=0}
		{assign var=total_lab value=0}
		{assign var=total_osc value=0}
		{assign var=total_ohd value=0}
		{foreach name=datagrid item=model from=$mfoperations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="op_no"}
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="volume_target" class='numeric'}
					{$model->volume_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="volume_uom"}
					{$model->volume_uom}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="volume_period"}
					{$model->getFormatted('volume_period')}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="quality_target" class='numeric'}
					{$model->quality_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="uptime_taget" class='numeric'}
					{$model->uptime_target}
				{/grid_cell}
				{if $type == 'std'}
					{assign var=total_lab value=$total_lab+$model->std_lab}
					{assign var=total_ohd value=$total_ohd+$model->std_ohd}
					{grid_cell model=$model cell_num=9 field="std_lab"}
						{$model->std_lab|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=10 field="std_ohd"}
						{$model->std_ohd|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=11 field="std_cost"}
						{$model->std_cost|string_format:$string_format}
					{/grid_cell}
				{else}
					{assign var=total_lab value=$total_lab+$model->latest_lab}
					{assign var=total_ohd value=$total_ohd+$model->latest_ohd}
					{grid_cell model=$model cell_num=9 field="latest_lab"}
						{$model->latest_lab|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=10 field="latest_ohd"}
						{$model->latest_ohd|string_format:$string_format}
					{/grid_cell}
					{grid_cell model=$model cell_num=11 field="latest_cost"}
						{$model->latest_cost|string_format:$string_format}
					{/grid_cell}
				{/if}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
		{if $mfoperations->count() > 0}
			{assign var=total_cost value=$total_lab+$total_ohd}
			{assign var=grand_total_lab value=$grand_total_lab+$total_lab}
			{assign var=grand_total_ohd value=$grand_total_ohd+$total_ohd}
			{assign var=grand_total_cost value=$grand_total_cost+$total_cost}
			<tr>
				<td colspan="6">
					<strong>Total</strong>
				</td>
				<td  class='numeric'>
			       	<strong>{$total_lab|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_ohd|string_format:$string_format}</strong>
				</td>
				<td class='numeric'>
			       	<strong>{$total_cost|string_format:$string_format}</strong>
				</td>
			</tr>
		{/if}
	{/data_table}
	<p><strong>Outside Operations (Outside Contract)</strong></p>
	{data_table}
		{heading_row}
			{heading_cell field="op_no"}
				Op No.
			{/heading_cell}
			{heading_cell field="description"}
				Description
			{/heading_cell}
			{if $type == 'std'}
				{heading_cell field="std_osc" class='right'}
					Outside Contract
				{/heading_cell}
			{else}
				{heading_cell field="latest_osc" class='right'}
					Outside Contract
				{/heading_cell}
			{/if}
		{/heading_row}
		{assign var=total_mat value=0}
		{assign var=total_lab value=0}
		{assign var=total_osc value=0}
		{assign var=total_ohd value=0}
		{foreach name=datagrid item=model from=$mfoutsideops}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="op_no"}
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="description"}
					{$model->description}
				{/grid_cell}
				{if $type == 'std'}
					{assign var=total_osc value=$total_osc+$model->std_osc}
					{grid_cell model=$model cell_num=4 field="std_lab"}
						{$model->std_osc|string_format:$string_format}
					{/grid_cell}
				{else}
					{assign var=total_osc value=$total_osc+$model->latest_osc}
					{grid_cell model=$model cell_num=4 field="latest_osc"}
						{$model->latest_osc|string_format:$string_format}
					{/grid_cell}
				{/if}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
		{if $mfoutsideops->count() > 0}
			{assign var=total_cost value=$total_osc}
			{assign var=grand_total_osc value=$grand_total_osc+$total_osc}
			{assign var=grand_total_cost value=$grand_total_cost+$total_cost}
			<tr>
				<td colspan="2">
					<strong>Total</strong>
				</td>
				<td>
			       	<strong>{$total_osc|string_format:$string_format}</strong>
				</td>
			</tr>
		{/if}
	{/data_table}
	<p><strong>Total Costs</strong></p>
	{data_table}
		{heading_row}
			{if $type == 'std'}
				{heading_cell field="std_mat" class='right'}
					Materials
				{/heading_cell}
				{heading_cell field="std_lab" class='right'}
					Labour
				{/heading_cell}
				{heading_cell field="std_osc" class='right'}
					Outside Contract
				{/heading_cell}
				{heading_cell field="std_ohd" class='right'}
					Overhead
				{/heading_cell}
				{heading_cell field="std_cost" class='right'}
					Total
				{/heading_cell}
			{else}
				{heading_cell field="latest_mat" class='right'}
					Materials
				{/heading_cell}
				{heading_cell field="latest_lab" class='right'}
					Labour
				{/heading_cell}
				{heading_cell field="latest_osc" class='right'}
					Outside Contract
				{/heading_cell}
				{heading_cell field="latest_ohd" class='right'}
					Overhead
				{/heading_cell}
				{heading_cell field="latest_cost" class='right'}
					Total Cost
				{/heading_cell}
			{/if}
		{/heading_row}
		{grid_row model=$stitem}
			{if $type == 'std'}
				{grid_cell model=$stitem cell_num=2 field="std_mat"}
					{$grand_total_mat|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=3 field="std_lab"}
					{$grand_total_lab|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=4 field="std_osc"}
					{$grand_total_osc|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=5 field="std_ohd"}
					{$grand_total_ohd|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=6 field="std_cost"}
					{$grand_total_cost|string_format:$string_format}
				{/grid_cell}
			{else}
				{grid_cell model=$stitem cell_num=2 field="latest_mat"}
					{$grand_total_mat|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=3 field="latest_lab"}
					{$grand_total_lab|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=4 field="latest_osc"}
					{$grand_total_osc|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=5 field="latest_ohd"}
					{$grand_total_ohd|string_format:$string_format}
				{/grid_cell}
				{grid_cell model=$stitem cell_num=6 field="latest_cost"}
					{$grand_total_cost|string_format:$string_format}
				{/grid_cell}
			{/if}
		{/grid_row}
	{/data_table}
{/content_wrapper}