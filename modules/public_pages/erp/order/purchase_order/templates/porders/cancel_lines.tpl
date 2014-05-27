{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.4 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$porder}
				<dt class="heading">{$porder->getFormatted('type')|cat:' Details'}</dt>
					{view_data attribute="order_number" label=$porder->getFormatted('type')|cat:' number'}
					{view_data attribute="supplier" label='supplier'}
					{view_data attribute="ext_reference" label='supplier_reference'}
					{view_data attribute="status"}
				<dt class="heading">Timescale</dt>
					{view_data attribute="order_date" label=$porder->getFormatted('type')|cat:' date'}
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
			{with model=$porder}
				{view_section heading="further_details"}
					{view_data attribute="currency"}
					{view_data attribute="net_value"}
					{view_data attribute="base_net_value"}
				{/view_section}
			{/with}
		</dl>
		<div id="view_data_bottom">
		{form controller="porders" action="save_cancel_lines" notags=true}
			<table id="order_lines" cellspacing=15>
				<thead>
					<tr>
						<th align='right'>Line #</th>
						<th align='left'>Item</th>
						<th align='right'>Unit price</th>
						<th align='right'>Original</th>
						<th align='right'>Revised</th>
						<th align='right'>Net Value</th>
						<th align='center'>Due Date</th>
						<th align='right'>Cancel?</th>
					</tr>
				</thead>
				{with model=$porder}
					{input type='hidden'  attribute='id' }
				{/with}
				{assign var=count value=0}
				{foreach name=lines item=line from=$porder->lines}
					{if $line->status==$line->newStatus() || $line->status==$line->lineAwaitingDelivery()}
						<tr>
							<td align='right'>{$line->line_number}</td>
							<td align='left'>{$line->item_description}</td>
							<td align='right'>{$line->price|string_format:"%.2f"}</td>
							<td align='right'>{$line->order_qty}</td>
							<td align='right'>{$line->revised_qty}</td>
							<td align='right'>{$line->net_value|string_format:"%.2f"}</td>
							<td align='center'>{$line->getFormatted('due_delivery_date')}</td>
							<td align='right'><input type='checkbox' name=POrderLine[cancel_line][{$line->id}] id=POrderLine_cancel_line_{$line->id} value=true></td>
						</tr>
					{/if}
				{/foreach}
			</table>
			{submit tags='none'}
		{/form}
		{include file='elements/cancelForm.tpl' tags='none'}
	</div>
{/content_wrapper}