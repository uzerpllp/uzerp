o{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.19 $ *}	
{content_wrapper}
	<div id="view_page" class="clearfix">
		{view_section heading=$POrder->getFormatted('type')|cat:' Header' expand="open"}
			<dl class="float-left">
				{with model=$POrder}
					{view_section heading=$POrder->getFormatted('type')|cat:' Details' expand="open"}
						{view_data attribute="order_number" label=$POrder->getFormatted('type')|cat:' number'}
						{view_data attribute="supplier" label='supplier'}
						<dt>Goods Received Number</dt>
						<dd>
							{foreach name=list key=grn_id item=grn from=$grns}
								{link_to module='goodsreceived' controller='poreceivedlines' action='view' id=$grn_id value=$grn}
								{if !$smarty.foreach.list.last}
									,
								{/if}
							{foreachelse}
								-
							{/foreach}
						</dd>
						{view_data attribute="ext_reference" label='supplier_reference'}
						{view_data attribute="status" value=$POrder->getFormatted('status')}
						{view_data attribute="due_date"}
						{view_data attribute="net_value"}
						{view_data attribute="delivery_term"}
					{/view_section}
				{/with}
			</dl>
			<dl class="float-right">
				{with model=$POrder}
					{view_section heading="Created/Updated" expand="closed"}
						{view_data attribute="order_date" label=$POrder->getFormatted('type')|cat:' date'}
						{view_data attribute="owner" label='current owner'}
						{view_data attribute="raised_by" label='raised_by'}
						{view_data attribute="created" label='altered_by'}
						{view_data attribute="alteredby" label='altered_by'}
						{view_data attribute="lastupdated" label='updated_on'}
					{/view_section}
					{view_section heading="Authorisation" expand="closed"}
						{view_data attribute="authorised_by" label='authorised_by'}
						{view_data attribute="date_authorised" label='authorised_on'}
						{view_section heading="Authorisers" expand="closed"}
							{foreach item=authorisedusers from=$authorised_users}
								{view_data value=$authorisedusers}
							{foreachelse}
								{view_data value="No authorisers"}
							{/foreach}
						{/view_section}
					{/view_section}
				{/with}
				{with model=$delivery_address}
					{view_section heading="delivery_address" expand="closed"}
						{view_data attribute="street1"}
						{view_data attribute="street2"}
						{view_data attribute="street3"}
						{view_data attribute="town"}
						{view_data attribute="county"}
						{view_data attribute="postcode"}
						{view_data attribute="country"}
						{if $use_sorder_delivery == 't'}
						{view_data value="Yes" label='Override with SO delivery address' id="deliv_override"}
						{/if}
					{/view_section}
				{/with}
				{with model=$POrder}
					{view_section heading="Currency/Order Value" expand="closed"}
						{view_data attribute="currency"}
						{view_data attribute="net_value"}
						{view_data attribute="base_net_value"}
					{/view_section}
					{view_section heading="Details" expand="closed"}
						{view_data attribute="description" tags=none label='Description'}
						{view_data attribute="sorder_number" label='Sales Order'}
						{view_data attribute="use_sorder_delivery" label='Use SO Delivery Address'}
						{view_data attribute="project_id" label='Project'}
						{view_data attribute="task_id" label='Task'}
					{/view_section}
					{if isset($linevalue)}
						{view_section heading="Order Line Summary" expand="closed"}
							{foreach key=status item=value from=$linevalue}
								{if $value>0}
									{view_data attribute=$status value=$value|string_format:"%.2f"}
								{/if}
							{/foreach}
						{/view_section}
					{/if}
				{/with}
			</dl>
			
		{/view_section}
		{view_section heading=$POrder->getFormatted('type')|cat:' Lines' expand="open"}
			<div id="view_data_bottom">
				{data_table}
					<thead>
						<tr>
							<th class='right'>Line #</th>
							<th align='left'>Description</th>
							<th class='right'>Unit price</th>
							<th class='right'>Order Qty</th>
							<th class='right'>Outstanding</th>
							<th class='right'>Revised</th>
							<th class='right'>Delivered</th>
							<th class='right'>Net Value</th>
							<th align='left'>Due Date</th>
							<th align='left'>Actual Delivery Date</th>
							<th align='left'>Status</th>
						</tr>
					</thead>
					{foreach name=lines item=line from=$porderlines}
						{assign var=line_number value=$line->line_number}
						{assign var=id value=$line->id}
						<tr data-line-number="{$line_number}" >
							{if $can_edit && $line->status==$line->newStatus()}
								<td align='right' class="edit-line">
									{link_to module="purchase_order" controller="porderlines" action="edit" id="$id" value="$line_number"}
									{input model=$line type='hidden' rowid="$line_number" attribute='id'}
								</td>
							{elseif $can_edit && $line->status!=$line->cancelStatus() && $line->status!=$line->invoiceStatus()}
								<td align='right' class="edit-line">
									{link_to module="purchase_order" controller="porderlines" action="update_glcodes" id="$id" value="$line_number"}
									{input model=$line type='hidden' rowid="$line_number" attribute='id'}
								</td>
							{else}
								<td align='right'>{$line->line_number}</td>
							{/if}
							<td align='left'>
								{if !is_null($line->stitem_id)}
									{assign var=stitem_id value=$line->stitem_id}
									{assign var=description value=$line->description}
									{link_to module="manufacturing" controller="stitems" action="view" id="$stitem_id" value="$description"}
								{else}
									{$line->description}
								{/if}
							</td>
							<td align='right'>{$line->price|string_format:"%.4f"}</td>
							<td align='right'>{$line->order_qty}</td>
							<td align='right'>{$line->os_qty}</td>
							<td align='right'>{$line->revised_qty}</td>
							<td align='right'>{$line->del_qty}</td>
							<td align='right'>{$line->net_value|string_format:"%.2f"}</td>
							<td align='left'>{$line->due_delivery_date}</td>
							<td align='left'>{$line->actual_delivery_date}</td>
							<td align='left'>{$line->getFormatted('status')}</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td align='left' colspan='10'>Account: {$line->glaccount},&nbsp;&nbsp;&nbsp;&nbsp;Centre {$line->glcentre}</td>
						</tr>
					{/foreach}
				{/data_table}
			</div>
		{/view_section}
	</div>
	<div id="editline">
	</div>
{/content_wrapper}