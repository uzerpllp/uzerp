{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction attribute="wo_number" label="Order No."}
			{view_data model=$transaction attribute="stitem" label="Stock Item"}
			{view_data model=$transaction attribute="required_by"}
			{view_data model=$transaction attribute="order_qty"}
			{view_data model=$transaction attribute="made_qty"}
		</dl>
	</div>
	{paging}
		{data_table}
			{heading_row}
				{heading_cell field="op_no" class='right'}
					Op No.
				{/heading_cell}
				{heading_cell field="remarks"}
					Remarks
				{/heading_cell}
				{heading_cell field="centre"}
					Centre
				{/heading_cell}
				{heading_cell field="resource"}
					Resource
				{/heading_cell}
				{heading_cell field="resource_qty" class='right'}
					Resource Qty
				{/heading_cell}
				<th class='right'>
					Time Required
				</th>
				<th class='right'>
					Time Used
				</th>
			{/heading_row}
			{assign var='adjusted' value='100'}
			{foreach name=datagrid item=model from=$mfoperations}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="op_no" class='numeric'}
						{$model->op_no}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="remarks"}
						{$model->remarks}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="centre"}
						{$model->centre}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="resource"}
						{$model->resource}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="resource_qty" class='numeric'}
						{$model->resource_qty}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="resource_qty" class='numeric'}
						{if $model->volume_target>0}
							{($stockitem->convertToUoM($stockitem->uom_id,$model->volume_uom_id,$transaction->order_qty)/$model->volume_target)|round:2} {$model->getFormatted('volume_period')}
						{/if}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="resource_qty" class='numeric'}
						{if $model->volume_target>0}
							{($stockitem->convertToUoM($stockitem->uom_id,$model->volume_uom_id,$transaction->made_qty)/$model->volume_target)|round:2} {$model->getFormatted('volume_period')}
						{/if}
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