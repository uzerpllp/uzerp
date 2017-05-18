{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$transaction}
			<dl id="view_data_left">
				{view_section heading="General"}
					{view_data attribute='item_code'}
					{view_data attribute='description'}
					{view_data attribute='abc_class'}
					{view_data attribute='comp_class'}
					{view_data attribute='alpha_code'}
					{view_data attribute='product_group'}
					{view_data attribute='type_code'}
					{view_data label='Unit of Measure' attribute='uom_name'}
					{view_data attribute='balance' label='Total Balance'}
					{view_data attribute='currentBalance()' label='Available Balance'}
					{view_data attribute='qty_decimals'}
					{view_data attribute='batch_size' }
					{view_data attribute='lead_time' }
					{view_data attribute='tax_rate'}
					{view_data attribute='obsolete_date'}
					{view_data attribute='min_qty' label='Minimum Qty'}
					{view_data attribute='max_qty' label='Maximum Qty'}
					{view_data attribute='float_qty'}
					{view_data attribute='free_qty'}
					{view_data attribute='price'}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="Costing"}
					{view_data attribute='cost_decimals'}
				{/view_section}
				{view_section heading="Standard Cost"}
					{view_data label='Total Cost' attribute='std_cost'}
					{view_data label='Materials' attribute='std_mat'}
					{view_data label='Labour' attribute='std_lab'}
					{view_data label='Outside Contract' attribute='std_osc'}
					{view_data label='Overhead' attribute='std_ohd'}
				{/view_section}
				{view_section heading="Latest Cost"}
					{view_data label='Total Cost' attribute='latest_cost'}
					{view_data label='Materials' attribute='latest_mat'}
					{view_data label='Labour' attribute='latest_lab'}
					{view_data label='Outside Contract' attribute='latest_osc'}
					{view_data label='Overhead' attribute='latest_ohd'}
				{/view_section}
				{view_section heading="Other Info"}
					{view_data attribute='ref1' label='Reference'}
					{view_data attribute='text1' label='Additional Text'}
					{view_data attribute='created' label='Created'}
					{view_data attribute='createdby' label='Created By'}
					{view_data attribute='lastupdated' label='Last Updated'}
					{view_data attribute='alteredby' label='Altered by'}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}