{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$ARGroup}
			{view_data attribute="description"}
			{view_data attribute="depn_method" label='Depreciation Method'}
			{view_data attribute="depn_term" label='Depreciation Term (years)'}
			{view_data attribute="depn_percent" label='Depreciation Percentage'}
			{view_data attribute="depn_percent_yr1" label='Depreciation Percentage (Year1)'}
			{view_data attribute="asset_cost_account"}
			{view_data attribute="asset_depreciation_account"}
			{view_data attribute="depreciation_charge_account"}
			{view_data attribute="disposals_account"}
			{view_data attribute="depn_first_year" label='Depreciate whole of first year'}
		{/with}
	</div>
{/content_wrapper}