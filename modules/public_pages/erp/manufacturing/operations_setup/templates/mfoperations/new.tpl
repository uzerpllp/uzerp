{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="mfoperations" action="save"}
		{with model=$models.MFOperation legend="MFOperation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='op_no' class="compulsory" }
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{select label='Stock Item' attribute='stitem_id' }
			{select label='Work Centre' attribute='mfcentre_id' }
			{input type='text'  attribute='remarks' }
			{input type='text'  attribute='volume_target' }
			<dt><label for="uom_list">Volume UoM</label>:</dt>
			<dd>
				<select id="uom_list" name="MFOperation[volume_uom_id]">
					{html_options options=$uom_list selected=$model->volume_uom_id}
				</select>
			</dd>
			{select label='Per' attribute='volume_period' }
			{input type='text'  label="Quality Target(%)" attribute='quality_target' }
			{input type='text'  label="Uptime Target(%)" attribute='uptime_target' }
			{select label='Resource' attribute='mfresource_id' }
			{input type='text'  attribute='resource_qty' }
		{/with}
		{submit}
	{/form}
	{if $mfoperations->count()>0}
	<p><strong>Current Structure</strong></p>
	{data_table}
		{heading_row}
			{heading_cell field="op_no"}
				Op No.
			{/heading_cell}
			{heading_cell field="start_date"}
				Start Date
			{/heading_cell}
			{heading_cell field="end_date"}
				End Date
			{/heading_cell}
			{heading_cell field="volume_target"}
				Volume Target
			{/heading_cell}
			{heading_cell field="volume_uom_id"}
				UoM
			{/heading_cell}
			{heading_cell field="volume_period"}
				Volume Period
			{/heading_cell}
			{heading_cell field="quality_target"}
				Quality Target
			{/heading_cell}
			{heading_cell field="uptime_target"}
				Uptime Target
			{/heading_cell}
			{heading_cell field="mfresource_id"}
				Resource
			{/heading_cell}
			{heading_cell field="resource_qty"}
				Resource Qty
			{/heading_cell}
		{/heading_row}
		{foreach name=datagrid item=model from=$mfoperations}
			{grid_row model=$model}
				{grid_cell model=$model cell_num=2 field="op_no"}
					{$model->op_no}
				{/grid_cell}
				{grid_cell model=$model cell_num=3 field="start_date"}
					{$model->start_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=4 field="end_date"}
					{$model->end_date}
				{/grid_cell}
				{grid_cell model=$model cell_num=6 field="volume_target"}
					{$model->volume_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=5 field="volume_uom_id"}
					{$model->volume_uom_id}
				{/grid_cell}
				{grid_cell model=$model cell_num=7 field="volume_period"}
					{$model->getFormatted('volume_period')}
				{/grid_cell}
				{grid_cell model=$model cell_num=8 field="quality_target"}
					{$model->quality_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=9 field="uptime_target"}
					{$model->uptime_target}
				{/grid_cell}
				{grid_cell model=$model cell_num=10 field="mfresource_id"}
					{$model->mfresource_id}
				{/grid_cell}
				{grid_cell model=$model cell_num=11 field="resource_qty"}
					{$model->resource_qty}
				{/grid_cell}
			{/grid_row}
		{foreachelse}
			<tr><td colspan="0">No matching records found!</td></tr>
		{/foreach}
	{/data_table}
	{/if}
{/content_wrapper}