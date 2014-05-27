{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	{form controller="stuomconversions" action="save"}
		{with model=$models.STuomconversion legend="STuomconversion Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{if $stitem_id<>""}
			  {view_data label='Stock Item' value=$stitem}
			  {input type='hidden'  attribute='stitem_id' value=$stitem_id}
			{else}
			  {select  label='Stock Item' attribute='stitem_id' class="compulsory" }
			{/if}
			{view_data label='Base UoM' value=$stitem_uom_name}
			{input type='hidden'  attribute='stitem_uom_id' value=$stitem_uom_id}
			{input type='hidden'  attribute='stitem_uom_name' value=$stitem_uom_name}
			{if $stitem_uom_id<>'' && $action=='new'}
				{select  attribute='from_uom_id' class="compulsory" label='One' value=$stitem_uom_id}
			{else}
				{select  attribute='from_uom_id' class="compulsory" label='One'}
			{/if}
			{input type='text'  attribute='conversion_factor' class="compulsory" label='contains'}
			{if $stitem_uom_id<>'' && $action=='new'}
				{select  attribute='to_uom_id' class="compulsory" label='of' value=$stitem_uom_id}
			{else}
				{select  attribute='to_uom_id' class="compulsory" label='of'}
			{/if}
		{/with}
		{submit}
		{include file='elements/saveAnother.tpl'}
	{/form}
	{include file='elements/cancelForm.tpl'}
	{if $elements->count()>0}
		<p><strong>Current Conversions</strong></p>
		{data_table}
			{heading_row}
				{heading_cell field="from_uom_name"}
					From UoM
				{/heading_cell}
				{heading_cell field="conversion_factor"}
					Conversion Factor
				{/heading_cell}
				{heading_cell field="to_uom_name"}
					To UoM
				{/heading_cell}
			{/heading_row}
			{foreach name=datagrid item=model from=$elements}
				{grid_row model=$model}
					{grid_cell model=$model cell_num=3 field="from_uom_name" }
						One {$model->from_uom_name}
					{/grid_cell}
					{grid_cell model=$model cell_num=5 field="conversion_factor" }
						contains {$model->conversion_factor}
					{/grid_cell}
					{grid_cell model=$model cell_num=4 field="to_uom_name" }
						{$model->to_uom_name}
					{/grid_cell}
				{/grid_row}
			{foreachelse}
				<tr><td colspan="0">No matching records found!</td></tr>
			{/foreach}
		{/data_table}
	{/if}
{/content_wrapper}