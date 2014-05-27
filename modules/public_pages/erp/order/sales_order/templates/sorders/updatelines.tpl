{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$sorder}
				<dt class="heading">{$sorder->getFormatted('type')|cat:' Details'}</dt>
					{view_data attribute="order_number" label=$sorder->getFormatted('type')|cat:' number'}
					{view_data attribute="customer" label='customer'}
					{view_data attribute="ext_reference" label='customer_reference'}
					{view_data attribute="status"}
				<dt class="heading">Timescale</dt>
					{view_data attribute="order_date" label=$sorder->getFormatted('type')|cat:' date'}
					{view_data attribute="despatch_date"}
					{view_data attribute="due_date"}
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
			{with model=$sorder}
				{view_section heading="further_details"}
					{view_data attribute="currency"}
					{view_data attribute="net_value"}
					{view_data attribute="base_net_value"}
				{/view_section}
			{/with}
		</dl>
		<div id="view_data_bottom">
		{form controller="sorders" action="saveupdatelines" notags=true}
		<table id="order_lines" cellspacing=15>
			<thead>
				<tr>
					<th align='right'>Line #</th>
					<th align='left'>Item</th>
					<th align='right'>Unit price</th>
					<th align='right'>Outstanding</th>
					<th align='right'>Net Value</th>
					<th align='right'>Revised</th>
					<th align='center'>Due Despatch Date</th>
					<th align='left'>Status</th>
				</tr>
			</thead>
			{with model=$sorder}
				{input type='hidden'  attribute='id' }
				{input type='hidden'  attribute='order_date' }
				{input type='hidden'  attribute='due_date' }
				{input type='hidden'  attribute='despatch_date' }
				{input type='hidden'  attribute='rate' }
				{input type='hidden'  attribute='twin_rate' }
				{input type='hidden'  attribute='net_value' }
				{input type='hidden'  attribute='twin_net_value' }
				{input type='hidden'  attribute='base_net_value' }
				{input type='hidden'  attribute='currency_id' }
			{/with}
			{assign var=count value=0}
		{foreach name=lines item=line from=$sorder->lines}
		{if $line->status==$line->newStatus()}
			{assign var=count value=$count+1}
			<tr>
				<input type='hidden' name="SOrderLine[id][]" value='{$line->id}' >
				<input type='hidden' name="SOrderLine[line_number][]" value='{$line->line_number}' >
				<input type='hidden' name="SOrderLine[price][]" value='{$line->price}' >
				<input type='hidden' name="SOrderLine[rate][]" value='{$line->rate}' >
				<input type='hidden' name="SOrderLine[twin_rate][]" value='{$line->twin_rate}' >
				<input type='hidden' name="SOrderLine[net_value][]" value='{$line->net_value}' >
				<input type='hidden' name="SOrderLine[twin_net_value][]" value='{$line->twin_net_value}' >
				<input type='hidden' name="SOrderLine[base_net_value][]" value='{$line->base_net_value}' >
				<input type='hidden' name="SOrderLine[del_qty][]" value='{$line->del_qty}' >
				<td align='right'>{$line->line_number}</td>
				<td align='left'>{$line->item_description}</td>
				<td align='right'>{$line->price|string_format:"%.2f"}</td>
				<td align='right'>{$line->os_qty}</td>
				<td align='right'>{$line->net_value|string_format:"%.2f"}</td>
				<td align='right' width=25>
					<input type='hidden' name="SOrderLine[lastupdated][]" id="sorderline_lastupdated_{$count}" value="{$line->lastupdated}">
					{if ($qty=='n')}
						<input type='hidden' name="SOrderLine[revised_qty][]" id="sorderline_revised_qty_{$count}" value="{$line->revised_qty}">
						{$line->revised_qty}
					{else}
						<input type='text' name="SOrderLine[revised_qty][]" id="sorderline_revised_qty_{$count}" value="{$line->revised_qty}" size=5 align='right' class="numeric">
					{/if}
				</td>
				<td align='left' width=110>
					<input type='text' name="SOrderLine[due_despatch_date][]" id="sorderline_due_despatch_date_{$count}" value="{$line->due_despatch_date|un_fix_date}" size=10 align='right' class="datefield">
				</td>
				<td align='left'>{$line->getFormatted('status')}</td>
				</td>
			</tr>
		{/if}
		{/foreach}
		</table>
		{submit tags='none'}
		{/form}
		{include file='elements/cancelForm.tpl' tags='none'}
	</div>
	</div>
{/content_wrapper}