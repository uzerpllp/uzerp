{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="sttransactions" action="saveStatus"}
		{with model=$transaction legend="STTransaction Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{if $status}
				{input type='hidden'  attribute='status' }
			{/if}
			<div id="view_page" class="clearfix">
				<dl>
					{if $status}
						<dt>Change Status to</dt>
						<dd>{$transaction->getFormatted('status')}</dd>
					{else}
						{select label='Change Status to' attribute='status'}
					{/if}
					<dt>Current Balance</dt>
					<dd>{$transaction->current_balance()}</dd>
					{if $status=='R'}
						{assign var=error_qty value=$transaction->error_qty*-1}
						{input type="text" value=$error_qty attribute="revised_qty" label=Quantity}
					{else}
						{input type="hidden" value="0" attribute="revised_qty"}
					{/if} 
					{input type='text' attribute='remarks'}
					<dt class="submit"></dt>
					<dd class="submit">{submit tags='none'}</dd>
				</dl>
			</div>
		{/with}
	{/form}
{/content_wrapper}