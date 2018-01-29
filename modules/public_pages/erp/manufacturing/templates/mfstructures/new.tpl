{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.10 $ *}
{content_wrapper}
	{form controller="mfstructures" action="save"}
		<dl>
		{with model=$models.MFStructure legend="MFStructure Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{if $stitem}
				{view_data label='Stock Item' value=$stitem}
				{input type='hidden'  attribute='stitem_id'}
			{else}
				{select attribute='stitem_id' options=$stitems label='Stock Item'}
			{/if}
			{input type='text'  attribute='line_no' class="compulsory" }
			{input type='date'  attribute='start_date' }
			{input type='date'  attribute='end_date' }
			{select  attribute='ststructure_id' label='Uses' options=$ststructures nonone=true}
			{input type='text'  attribute='qty' }
			{select attribute='uom_id' options=$uom_list selected=$uom_id}
			{input type='text'  attribute='remarks' }
			{input type='text'  attribute='waste_pc' }
		{/with}
		<dt class="submit"></dt>
		<dd class="submit">
			<input class="formsubmit uz-validate" value="Save" name="saveform" id="saveform" type="submit">
			<input class="formsubmit uz-validate" value="Save and Add Another" name="saveadd" id="saveadd" type="submit">
			<a href="{$cancel_link}">Cancel</a>
		</dd>
		</dl>
	{/form}
	{if $mfstructures->count()>0}
		<p><strong>Current Structure</strong></p>
		{data_table}
			{heading_row}
				{heading_cell field="line_no"}
					Line No.
				{/heading_cell}
				{heading_cell field="ststructure"}
					Stock Item
				{/heading_cell}
				{heading_cell field="start_date"}
					Start Date
				{/heading_cell}
				{heading_cell field="end_date"}
					End Date
				{/heading_cell}
				{heading_cell field="qty"}
					Quantity
				{/heading_cell}
				{heading_cell field="uom"}
					UoM
				{/heading_cell}
				{heading_cell field="waste_pc"}
					Waste %
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$mfstructures}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=1 field="line_no"}
						{$model->line_no}
					{/grid_cell}
					{grid_cell model=$model cell_num=2 field="ststructure"}
						{$model->ststructure}
					{/grid_cell}
					{grid_cell model=$model cell_num=3 field="start_date"}
						{$model->start_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="end_date"}
						{$model->end_date}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="qty"}
						{$model->qty}
					{/grid_cell}
					{grid_cell model=$model cell_num=6 field="uom"}
						{$model->uom}
					{/grid_cell}
					{grid_cell model=$model cell_num=7 field="waste_pc"}
						{$model->waste_pc}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		{/data_table}
	{/if}
{/content_wrapper}