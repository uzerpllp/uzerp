{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	{form controller="assets" action="save"}
		{with model=$models.Asset legend="Asset Master Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='usercompanyid' }
			<dl id="view_data_left">
				{input type='text' attribute='code' class="compulsory" }
				{textarea attribute='description' class="compulsory" }
				{input type='text' attribute='serial_no' class="compulsory" }
				{select attribute='argroup_id' class="compulsory" label='Asset Group'}
				{select attribute='arlocation_id' class="compulsory" label='Asset Location'}
				{select attribute='aranalysis_id' class="compulsory" label='Asset Analysis'}
				{select attribute='plmaster_id' class="compulsory" label='Supplier'}
				{input type='date' attribute='purchase_date' class="compulsory" }
				{input type='text' attribute='purchase_price' class="numeric" }
				{input type='checkbox' attribute='leased' }
			</dl>
			<dl id="view_data_right">
				{input type='text' attribute='bfwd_value' class="numeric" label='Brought forward value'}
				{input type='text' attribute='ty_depn' class="numeric" label='Depreciation this year'}
				{input type='text' attribute='td_depn' class="numeric" label='Depreciation to date'}
				{input type='text' attribute='wd_value' class="numeric" label='Written Down Value'}
				{input type='text' attribute='residual_value' class="numeric" }
				{input type='date' attribute='disposal_date' class="compulsory" }
			</dl>
		{/with}
		<div id="view_data_bottom">
			{submit}
			{submit value='Save and Add Another'}
		</div>
	{/form}
	<div id="view_data_bottom">
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}