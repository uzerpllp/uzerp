{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
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
					{view_data attribute="fulladdress" label=''}
				{/view_section}
			{/with}
			{with model=$sorder}
				{view_section heading="further_details"}
					{view_data attribute="currency"}
					{view_data attribute="net_value"}
					{view_data attribute="base_net_value"}
				{/view_section}
				{view_section heading="Order Line Summary"}
					{foreach key=status item=value from=$linevalue}
						{if $value>0}
							{view_data attribute=$status value=$value|string_format:"%.2f"}
						{/if}
					{/foreach}
				{/view_section}
			{/with}
		</dl>
		<div id="view_data_bottom">
		{form controller="sorders" action="printDialog" notags=true}
			{input type='hidden' name='printaction' value='printproformainvoice'}
			{input type='hidden' name='filename' value=$filename}
			<table id="order_lines" cellspacing=15>
				<thead>
					<tr>
					<th align='right'>Line #</th>
					<th align='left'>Description</th>
					<th align='right'>Unit price</th>
					<th align='right'>Order Qty</th>
					<th align='right'>UoM</th>
					<th align='right'>Outstanding</th>
					<th align='right'>Revised</th>
					<th align='right'>Despatched</th>
					<th align='right'>Net Value</th>
					<th align='left'>Due Despatch Date</th>
					<th align='left'>Actual Despatch Date</th>
					<th align='left'>Status</th>
					<th align='left'>Select?</th>
					</tr>
				</thead>
				{with model=$sorder}
					{input type='hidden'  attribute='id' }
				{/with}
				{assign var=count value=0}
				{foreach name=lines item=line from=$sorder->lines}
					{if $line->status!=$line->cancelStatus() && $line->status!=$line->invoicedStatus()}
						<tr>
							<td align='right'>{$line->line_number}</td>
							<td align='left'>{$line->description}</td>
							<td align='right'>{$line->price|string_format:"%.2f"}</td>
							<td align='right'>{$line->order_qty}</td>
							<td align='right'>{$line->uom_name}</td>
							<td align='right'>{$line->os_qty}</td>
							<td align='right'>{$line->revised_qty}</td>
							<td align='right'>{$line->del_qty}</td>
							<td align='right'>{$line->net_value|string_format:"%.2f"}</td>
							<td align='left'>{$line->getFormatted('due_despatch_date')}</td>
							<td align='left'>{$line->getFormatted('actual_despatch_date')}</td>
							<td align='left'>{$line->getFormatted('status')}</td>
							<td>{input type='checkbox' class="checkbox" model=$line attribute=select_line number=$line->id value=true tags=none}</td>
						</tr>
					{/if}
				{/foreach}
			</table>
			{submit _id="submit" value='Print' tags='none'}
		{/form}
		{include file='elements/cancelForm.tpl' tags='none'}
	</div>
{/content_wrapper}