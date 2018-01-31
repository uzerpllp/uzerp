{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{content_wrapper}
	{form controller="mfoutsideoperations" action="save"}
		<dl>
		{with model=$models.MFOutsideOperation legend="MFOutsideOperation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='op_no' class="compulsory" }
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			{select label='Stock Item' attribute='stitem_id' }
			{input type='text'  attribute='description' }
			{input type='text' label='Cost' attribute='latest_osc' }
		{/with}
			<dt class="submit"></dt>
    		<dd class="submit form-buttons-inline">
    			<input class="formsubmit uz-validate" value="Save" name="saveform" id="saveform" type="submit">
    			<input class="formsubmit uz-validate" value="Save and Add Another" name="saveadd" id="saveadd" type="submit">
    			<a href="{$cancel_link}">Cancel</a>
    		</dd>
		</dl>
	{/form}

	<div id='show_parts'>
        {if $mfoutsideoperations->count()>0}
        		<h2>Current Operations</h2>
        		{data_table}
        			{heading_row}
        				{heading_cell field="op_no"}
        					Op No.
        				{/heading_cell}
        				{heading_cell field="description"}
        					Description
        				{/heading_cell}
        				{heading_cell field="start_date"}
        					Start Date
        				{/heading_cell}
        				{heading_cell field="end_date"}
        					End Date
        				{/heading_cell}
        			{/heading_row}
        			{foreach name=datagrid item=model from=$mfoutsideoperations}
        				{grid_row model=$model}
        					{grid_cell model=$model cell_num=2 field="op_no"}
        						{$model->op_no}
        					{/grid_cell}
        					{grid_cell model=$model cell_num=3 field="description"}
        						{$model->description}
        					{/grid_cell}
        					{grid_cell model=$model cell_num=3 field="start_date"}
        						{$model->start_date}
        					{/grid_cell}
        					{grid_cell model=$model cell_num=4 field="end_date"}
        						{$model->end_date}
        					{/grid_cell}
        				{/grid_row}
        			{foreachelse}
        				<tr><td colspan="0">No matching records found!</td></tr>
        			{/foreach}
        		{/data_table}
        	{/if}
	</div>
{/content_wrapper}