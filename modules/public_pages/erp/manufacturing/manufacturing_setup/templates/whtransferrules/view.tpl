{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$transaction}
			<h2>Transfer Rule: {$transaction->action_name}</h2>
				<dl id="view_data_left">
					<h3>From</h3>
					{view_data label='Store' value=$from_store}
					{view_data label='Location' attribute="from_location"}
				</dl>
				<dl id="view_data_right">
					<h3>To</h3>
					{view_data label='Store' value=$to_store}
					{view_data label='Location' attribute="to_location"}
				</dl>
		{/with}
	</div>
{/content_wrapper}