{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.17 $ *}
{content_wrapper}
	{form controller="stitems" action="save"}
		{with model=$models.STItem legend="STItem Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{if $action == 'edit'}
				{if $model->balances->count()>0 || $model->workorders->count()>0 || $model->where_used->count()>0 || $model->wo_structures->count()>0}
					{assign var=inuse value=true}
				{/if}
			{/if}
			<div id="view_page" class="clearfix">
			    <dl class="float-left" >
					{input type='text'  attribute='item_code' class="compulsory" }
					{input type='text'  attribute='alpha_code' }
					{if $inuse}
						{view_data  attribute='uom_name' }
						{input type='hidden' attribute='uom_id' }
					{else}
						{select attribute='uom_id' }
					{/if}
					{select attribute='prod_group_id' }
					{if $inuse}
						{view_data  attribute='cost_decimals' }
						{view_data  attribute='qty_decimals' }
						{input type='hidden' attribute='cost_decimals' }
						{input type='hidden' attribute='qty_decimals' }
					{else}
						{input type='text'  attribute='cost_decimals' }
						{input type='text'  attribute='qty_decimals' }
					{/if}
					{input type='text'  attribute='min_qty' }
					{input type='text'  attribute='max_qty' }
					{select attribute='tax_rate_id' }
			    </dl>
			    <dl class="float-right">
					{input type='text'  attribute='description' }
					{if $inuse}
						{view_data  attribute='type_code_id' label='Type Code'}
						{view_data  attribute='comp_class' }
						{input type='hidden' attribute='type_code_id' }
						{input type='hidden' attribute='comp_class' }
					{else}
						{select attribute='type_code_id' }
						{select attribute='comp_class' }
					{/if}
					{select attribute='abc_class' }
					{if !$inuse || $model->comp_class=='B'}
						<div id="latest_mat_container">
							{input type='text' label='Materials Cost' attribute='latest_mat' }
						</div>
					{/if}
					{input type='text'  attribute='batch_size' }
					{input type='text'  attribute='lead_time' }
					{input type='text'  attribute='ref1' }
					{input type='text'  attribute='text1' }
					{input type='date'  attribute='obsolete_date' }
		    	</dl>
			</div>
		{/with}
		{submit}
		{submit value='Save and Add Another' name='saveadd' id='saveadd'}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}