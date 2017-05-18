{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$periodicpayment}
			<dl id="view_data_left">
				{view_data attribute='status'}
				{view_data attribute='source'}
				{view_data attribute='company'}
				{if $model->source=='CP' || $model->source=='CR'}
					{view_data attribute='person' }
				{/if}
				{view_data attribute='cb_account' label='Bank Account'}
				{view_data attribute='currency' }
				{view_data attribute='payment_type'}
				{view_data attribute='frequency' }
				{if $model->source=='CP' || $model->source=='CR'}
					{view_data attribute='glaccount' }
					{view_data attribute='glcentre' }
				{/if}
			</dl>
			<dl id="view_data_right">
				{view_data attribute="description" }
				{view_data attribute="ext_reference" }
				{view_data attribute="start_date" }
				{view_data attribute="next_due_date" }
				{view_data attribute="end_date" }
				{view_data attribute="occurs"}
				{view_data attribute="current" label='Payments Made'}
				{if $model->source=='CP' || $model->source=='CR'}
					{view_data attribute="net_value" }
					{view_data attribute='tax_rate' }
					{view_data attribute="tax_value" }
				{else}
					{view_data attribute="gross_value" }
				{/if}
				{view_data attribute="variable" }
				{view_data attribute="write_variance" }
			</dl>
		{/with}
	</div>
{/content_wrapper}