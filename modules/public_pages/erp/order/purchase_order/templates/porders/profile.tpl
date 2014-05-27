{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		{view_section heading=$POrder->getFormatted('type')|cat:' Header' expand='open'}
			<dl id="view_data_left">
				{with model=$POrder}
					{view_section heading=$POrder->getFormatted('type')|cat:' Details' expand='open'}
						{view_data attribute="order_number" label=$POrder->getFormatted('type')|cat:' number'}
						{view_data attribute="supplier" label='supplier'}
						{view_data attribute="ext_reference" label='supplier_reference'}
						{view_data attribute="status" value=$POrder->getFormatted('status')}
						{view_data attribute="due_date"}
						{view_data attribute="net_value"}
						{view_data attribute="delivery_term"}
					{/view_section}
				{/with}
			</dl>
			<dl id="view_data_right">
				{with model=$POrder}
					{view_section heading="Created/Updated" expand='closed'}
						{view_data attribute="order_date" label=$POrder->getFormatted('type')|cat:' date'}
						{view_data attribute="raised_by" label='raised_by'}
						{view_data attribute="owner" label='current owner'}
						{view_data attribute="alteredby" label='altered_by'}
						{view_data attribute="lastupdated" label='updated_on'}
					{/view_section}
					{view_section heading="Authorisation" expand='closed'}
						{view_data attribute="authorised_by" label='authorised_by'}
						{view_data attribute="date_authorised" label='authorised_on'}
						{view_section heading="Authorisers" expand='open'}
							{foreach item=authorisedusers from=$authorised_users}
								{view_data value=$authorisedusers}
							{foreachelse}
								{view_data value="No authorisers"}
							{/foreach}
						{/view_section}
					{/view_section}
				{/with}
				{with model=$delivery_address}
					{view_section heading="delivery_address" expand='closed'}
						{view_data attribute="street1"}
						{view_data attribute="street2"}
						{view_data attribute="street3"}
						{view_data attribute="town"}
						{view_data attribute="county"}
						{view_data attribute="postcode"}
						{view_data attribute="country"}
					{/view_section}
				{/with}
				{with model=$POrder}
					{view_section heading="Currency/Order Value" expand='closed'}
						{view_data attribute="currency"}
						{view_data attribute="net_value"}
						{view_data attribute="base_net_value"}
					{/view_section}
			{/with}
			</dl>
		{/view_section}
		{view_section heading=$POrder->getFormatted('type')|cat:' Lines' expand='open'}
			<div id="view_data_bottom">
				<table id="order_lines" cellspacing=15>
					<thead>
						<tr>
							<th align='left'>Account</th>
							<th align='left'>Centre</th>
							<th align='left'>Authorisers</th>
							<th align='right'>Sum</th>
						</tr>
					</thead>
					{foreach name=lines item=line from=$po_linesum}
						<tr>
							<td align='left'>{$line->glaccount}</td>
							<td align='left'>{$line->glcentre}</td>
							{assign var=key value=$line->id}
							<td align='left'>
								{if $line_authorisers.$key == ''}
									No authorisers
								{else}
									{$line_authorisers.$key}
								{/if}
							</td>
							<td align='right'>{$line->value|string_format:"%.2f"}</td>
						</tr>
					{/foreach}
				</table>
			</div>
		{/view_section}
	</div>
{/content_wrapper}