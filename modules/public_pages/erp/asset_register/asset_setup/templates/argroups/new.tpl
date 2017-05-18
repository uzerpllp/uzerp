{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="argroups" action="save"}
		{with model=$models.ARGroup legend="Asset Group Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='usercompanyid' }
			{input type='text' attribute='description' class="compulsory" }
			{select attribute='depn_method' class="compulsory" label='Depreciation Method'}
			{input type='text' attribute='depn_term' class="numeric" label='Depreciation Term (years)'}
			{input type='text' attribute='depn_percent' class="numeric" label='Depreciation Percentage'}
			{input type='text' attribute='depn_percent_yr1' class="numeric" label='Depreciation Percentage (Year1)'}
			{select attribute='asset_cost_glaccount_id' class="compulsory" label='Asset Cost Account'}
			{select attribute='asset_depreciation_glaccount_id' class="compulsory" label='Asset Depreciation Account'}
			{select attribute='depreciation_charge_glaccount_id' class="compulsory" label='Depreciation Charge Account'}
			{select attribute='disposals_glaccount_id' class="compulsory" label='Disposals Account'}
			{input type='checkbox' attribute='depn_first_year' label='Depreciate Whole of First Year'}
		{/with}
		{submit}
		{if $action!='edit'}
			{submit value='Save and Add Another'}
		{/if}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}