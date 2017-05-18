{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.16 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller=$controller action="save_pick_list"}
			<dl class="float-left">
				{with model=$SOrder}
					{view_section heading=$SOrder->getFormatted('type')|cat:' Details' expand="open"}
						{view_data attribute="order_number" label=$SOrder->getFormatted('type')|cat:' number'}
						{view_data attribute="customer" label='customer'}
						{select attribute="to_location_id" options=$from_locations label='Pick Into' nonone=true}
						{input type='hidden' attribute='id'}
					{/view_section}
				{/with}
			</dl>
			<div id="view_data_bottom">
				{data_table}
					{heading_row}
						{heading_cell field="line_number"}
							Line #
						{/heading_cell}
						{heading_cell field="item_description"}
							Item
						{/heading_cell}
						{heading_cell field="from"}
							Picked From
						{/heading_cell}
						{heading_cell field="order_qty" class='right'}
							Available Qty
						{/heading_cell}
						{heading_cell field="order_qty" class='right'}
							Order Qty
						{/heading_cell}
						{heading_cell field="del_qty" class='right'}
							Picked Qty
						{/heading_cell}
						{heading_cell field="uom_name"}
							UoM
						{/heading_cell}
						{heading_cell field="confirm"}
							Confirm Pick
						{/heading_cell}
						{heading_cell field="backorder"}
							Back Order<br>Difference
						{/heading_cell}
					{/heading_row}
					{foreach name=lines item=line from=$SOrder->lines}
						{assign var=key value=$line->id}
						{if $line->status==$line->newStatus() || $line->status==$line->partDespatchStatus()}
							{grid_row data_line=$line->id}
								{input type='hidden' model=$line rowid=$line->id number=$line->id attribute=stitem_id}
								{input type='hidden' model=$line rowid=$line->id number=$line->id attribute=line_number}
								{assign var=pick_qty value=$line->os_qty}
								{assign var=non_stock value=false}
								{assign var=no_stock value=false}
								{if is_null($line->stitem_id)}
									{assign var=non_stock value=true}
								{elseif empty($action_list.$key.locations)}
									{assign var=no_stock value=true}
								{elseif $action_list.$key.balance<$line->os_qty}
									{assign var=pick_qty value=$action_list.$key.balance}
								{/if}
								{grid_cell model=$line cell_num=2 field="line_number"}
									{$line->line_number}
								{/grid_cell}
								{grid_cell model=$line cell_num=2 field="item_description" no_escape=true}
									{if !is_null($line->stitem_id)}
										{assign var=stitem_id value=$line->stitem_id}
										{assign var=description value=$line->item_description}
										{link_to module="manufacturing" controller="stitems" action="view" id="$stitem_id" value="$description"}
									{else}
										{$line->item_description}
									{/if}
								{/grid_cell}
								{if $no_stock}
									<td>No Stock Available</td>
								{else}
									{if $non_stock}
										<td>Non Stock Item</td>
									{else}
										<td>
											{select model=$line attribute="whlocation_id" rowid=$key number=$key options=$action_list.$key.locations nonone=true tags=none label=' '}
										</td>
									{/if}
								{/if}
								<td class="numeric" id="balance{$line->id}">
									{if !$non_stock}
										{input type="text" model=$line readonly=true attribute="balance" rowid=$key number=$key value=$action_list.$key.balance class="numeric read-only balance" tags=none label=" "}
									{/if}
								</td>
								{grid_cell model=$line cell_num=2 field="os_qty" no_escape=true}
									{$line->os_qty}
									{input type="hidden" model=$line attribute="os_qty" rowid=$key number=$key}
								{/grid_cell}
								<td class="numeric">
									{if !$no_stock}
										{input type="text" model=$line attribute="del_qty" rowid=$key number=$key value=$pick_qty class="numeric del_qty" tags=none label=" "}
									{/if}
								</td>
								{grid_cell model=$line cell_num=2 field="uom_name"}
									{$line->uom_name}
								{/grid_cell}
								{if $no_stock}
									<td></td>
									<td></td>
								{else}
									<td align=center>
										{input type="checkbox" class="checkbox" model=$line attribute="id" rowid=$key number=$key tags=none label=' '}
									</td>
									<td align=center>
										{input type="checkbox" class="checkbox" model=$line attribute="backorder" number=$key tags=none label=' '}
									</td>
								{/if}
							{/grid_row}
						{/if}
					{/foreach}
					{grid_row}
						<td colspan=7 align='right'>
							<input type='button' value="Select All" class="select_all" style="width: 80px;">
						</td>
					{/grid_row}
				{/data_table}
				{submit}
			</div>
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
{/content_wrapper}