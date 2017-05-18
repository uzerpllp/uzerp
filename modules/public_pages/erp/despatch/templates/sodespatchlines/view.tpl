{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.9 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
		<dt class="heading">Despatch Note {$despatch_number}</dt>
		{with model=$order}
			<dt class="heading">{$order->getFormatted('type')|cat:' Details'}</dt>
				{view_data attribute="order_number" label=$order->getFormatted('type')|cat:' number'}
				{view_data attribute="customer" label='customer'}
				{view_data attribute="ext_reference" label='customer_reference'}
				{view_data attribute="status"}
			<dt class="heading">Timescale</dt>
				{view_data attribute="order_date" label=$order->getFormatted('type')|cat:' date'}
				{view_data attribute="despatch_date"}
				{view_data attribute="due_date"}
				{view_data value=','|implode:$order->despatch_from->rules_list('from_location') label='Despatch From'}
		{/with}
		</dl>
		<dl id="view_data_right">
			{with model=$delivery_address}
				{view_section heading="delivery_address"}
					{view_data attribute="street1"}
					{view_data attribute="street2"}
					{view_data attribute="street3"}
					{view_data attribute="town"}
					{view_data attribute="county"}
					{view_data attribute="postcode"}
					{view_data attribute="country"}
				{/view_section}
			{/with}
			{with model=$order}
				{view_section heading="description"}
					{view_data attribute="description" tags=none label=''}
				{/view_section}
			{/with}
		</dl>
		<div id="view_data_bottom">
		{data_table}
			<thead>
				<tr>
					<th>Item</th>
					<th>UoM</th>
					<th class='right'>Qty</th>
					<th>Despatch Date</th>
					<th class='right'>Stock</th>
					<th>Despatch From</th>
				</tr>
			</thead>
		{foreach name=lines item=line from=$despatchlines}
			<tr>
				<td align='left'>{$line->order_line_detail->item_description}</td>
				<td align='left'>{$line->uom_name}</td>
				<td align='right'>{$line->despatch_qty}</td>
				<td align='left'>{$line->getFormatted('despatch_date')}</td>
				<td class='numeric'>{$line->getStockBalance()}</td>
				<td align='left'>{','|implode:$line->despatch_from->rules_list('from_location')}</td>
			</tr>
		{/foreach}
		{/data_table}
	</div>
	</div>
{/content_wrapper}