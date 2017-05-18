{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.22 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{view_section heading=$SOrder->getFormatted('type')|cat:' Header' expand="open"}
			<dl class="float-left">
				{with model=$SOrder}
					{view_section heading=$SOrder->getFormatted('type')|cat:' Details' expand="open"}
						{view_data attribute="order_number" label=$SOrder->getFormatted('type')|cat:' number'}
						{view_data attribute="customer" label='customer'}
						{view_data attribute="ext_reference" label='customer_reference'}
						{view_data attribute="status" ddclass="show_value"}
					{/view_section}
					{view_section heading="Currency/Order Value" expand="closed"}
						{view_data attribute="currency"}
						{view_data attribute="net_value"}
						{view_data attribute="base_net_value"}
					{/view_section}
					{if isset($linevalue)}
						{view_section heading="Order Line Status Summary" expand="closed"}
							{foreach key=status item=value from=$linevalue}
								{if $value>0}
									{view_data name=$status value=$value|string_format:"%.2f" label=$status}
								{/if}
							{/foreach}
						{/view_section}
					{/if}
				{/with}
			</dl>
			<dl class="float-right">
				{view_section heading="delivery_address" expand="open"}
					{if $SOrder->person_id<>''}
						{with model=$SOrder}
							{view_data attribute="person"}
						{/with}
					{/if}
					{with model=$delivery_address}
						{view_data attribute="fulladdress" label=''}
						{view_data attribute="postcode" label='Link to Map'}
					{/with}
				{/view_section}
				{with model=$SOrder}
					{view_section heading="Created/Updated" expand="closed"}
						{view_data attribute="order_date" label=$SOrder->getFormatted('type')|cat:' date'}
						{view_data attribute="createdby" label='raised_by'}
						{view_data attribute="created" label='created_on'}
						{view_data attribute="alteredby" label='altered_by'}
						{view_data attribute="lastupdated" label='updated_on'}
					{/view_section}
					{view_section heading="Description" expand="closed"}
						{view_data attribute="description" tags=none label=''}
					{/view_section}
					{view_section heading="Project Details" expand="closed"}					
							{view_data attribute="project_id" label='Project'}
							{view_data  attribute="task_id" label='Task'}
					{/view_section}
					{view_section heading="Despatch" expand="closed"}
						{view_data attribute="despatch_date"}
						{view_data attribute="due_date"}
						{view_data attribute="despatch_from" value=','|implode:$SOrder->despatch_from->rules_list('from_location') label='Despatch From'}
						{view_data attribute="delivery_term"}
					{/view_section}
				{/with}
			</dl>
		{/view_section}
		{view_section heading=$SOrder->getFormatted('type')|cat:' Lines' expand="open"}
			<div id="view_data_bottom">
				{paging}
				{data_table}
					<thead>
						<tr>
							<th class='right'>Line #</th>
							<th align='left'>Description</th>
							<th class='right'>Unit price</th>
							<th class='right'>Order Qty</th>
							<th class='right'>UoM</th>
							<th class='right'>Outstanding</th>
							<th class='right'>Revised</th>
							<th class='right'>Despatched</th>
							<th class='right'>Net Value</th>
							<th align='left'>Due Despatch Date</th>
							<th align='left'>Actual Despatch Date</th>
							<th align='left'>Status</th>
						</tr>
					</thead>
					{foreach name=datagrid item=line from=$sorderlines}
						{assign var=line_number value=$line->line_number}
						{assign var=id value=$line->id}
						<tr data-line-number="{$line_number}">
							{if $line->status==$line->newStatus()}
								<td align='right' class="edit-line">
									{link_to module="sales_order" controller="sorderlines" action="edit" id="$id" value="$line_number"}
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
						</tr>
					{/foreach}
				{/data_table}
			</div>
		{/view_section}
	</div>
	<div id="editline"></div>
{/content_wrapper}