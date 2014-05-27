{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="assets" action="save"}
		{with model=$models.Asset legend="Asset Master Details"}
			{input type='hidden' attribute='id' }
			{input type='hidden' attribute='usercompanyid' }
			<dl id="view_data_left">
				{input type='text' attribute='code' class="compulsory" }
				{input type='text' attribute='serial_no' class="compulsory" }
				{textarea attribute='description' class="compulsory" }
				{select attribute='argroup_id' class="compulsory" label='Asset Group'}
				{select attribute='arlocation_id' class="compulsory" label='Asset Location'}
				{select attribute='aranalysis_id' class="compulsory" label='Asset Analysis'}
				{select attribute='plmaster_id' nonone=true class="compulsory" label='Supplier'}
				{input type='date' attribute='purchase_date' class="compulsory" }
				{input type='text' attribute='purchase_price' class="numeric" }
				{input type='checkbox' attribute='leased' class="numeric" }
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