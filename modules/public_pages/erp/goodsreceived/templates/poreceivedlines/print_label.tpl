{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.1 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller="poreceivedlines" action="confirm_print_labels"}
			<dl id="view_data_left">
				{with model=$POrder}
					<dt class="heading">Goods Received Note {$grn->gr_number}</dt>
					{view_data value=$grn->received_date label="received_date"}
					{view_data value=$grn->delivery_note label="delivery_note"}
					{view_data attribute="order_number" label=$POrder->getFormatted('type')|cat:' number'
					link_to='"module":"purchase_order","controller":"porders","action":"view","id":"'|cat:$POrder->id|cat:'"'}
					{view_data attribute="supplier" label='supplier'}
					{view_data attribute="ext_reference" label='supplier_reference'}
				{/with}
			</dl>
			<dl id="view_data_right">
				{include file='elements/select_printer.tpl'}
			</dl>
			<div id="view_data_bottom">
				<dt class="heading">Goods Received Details</dt>
				{data_table}
					{heading_row}
						{heading_cell field="item_description"}
							Item
						{/heading_cell}
						{heading_cell field="description"}
							Description
						{/heading_cell}
						{heading_cell field="received_date"}
							Date Received
						{/heading_cell}
						{heading_cell field="received_qty" class='right'}
							Quantity
						{/heading_cell}
						{heading_cell field="uom_name"}
							UoM
						{/heading_cell}
						{heading_cell field="pallet_count"}
							Pallets
						{/heading_cell}
						{heading_cell field="pallet_count"}
							Labels per Pallet
						{/heading_cell}
						{heading_cell field="pallet_count"}
							Qty per Pallet
						{/heading_cell}
						{heading_cell field="item_count"}
							Items
						{/heading_cell}
						{heading_cell field="item_qty"}
							Qty per Item
						{/heading_cell}
						{heading_cell field="print"}
							Print Labels
						{/heading_cell}
					{/heading_row}
					{foreach name=lines item=line from=$POReceivedlines}
						{grid_row model=$line}
							{grid_cell model=$line cell_num=2 field="item_description"}
								{$line->item_description}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="description"}
								{$line->order_line->description}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="received_date"}
								{$line->getFormatted('received_date')}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="received_qty" class='numeric' no_escape=true}
								{input type='hidden' model=$line attribute='gr_number' class='numeric' rowid=$line->id number=$line->id label='' tags=none}
								{input type='hidden' model=$line attribute='received_qty' class='numeric' rowid=$line->id number=$line->id label='' tags=none}
								{input type='hidden' model=$line attribute='received_date' class='numeric' rowid=$line->id number=$line->id label='' tags=none}
								{input type='hidden' model=$line attribute='qty_decimals' class='numeric' rowid=$line->id number=$line->id label='' tags=none}
								{input type='hidden' model=$line attribute='stitem' class='numeric' rowid=$line->id number=$line->id label='' tags=none }
								{$line->received_qty}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="uom_name"}
								{$line->uom_name}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="pallet_count" no_escape=true}
								{input model=$line attribute='pallet_count' class='pallet_count numeric' rowid=$line->id number=$line->id label='' tags=none value=1}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="pallet_labels" no_escape=true}
								{input model=$line attribute='pallet_labels' class='numeric' rowid=$line->id number=$line->id label='' tags=none value=1}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="pallet_qty" no_escape=true}
								{input model=$line attribute='pallet_qty' class='numeric' rowid=$line->id number=$line->id label='' tags=none value=$line->received_qty}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="item_count" no_escape=true}
								{input model=$line attribute='item_count' class='item_count numeric' rowid=$line->id number=$line->id label='' tags=none value=1}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="item_qty" no_escape=true}
								{input model=$line attribute='item_qty' class='numeric' rowid=$line->id number=$line->id label='' tags=none value=$line->received_qty}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field="print" no_escape=true}
								{input type='checkbox' model=$line attribute='print' rowid=$line->id number=$line->id label='' tags=none}
							{/grid_cell}
						{/grid_row}
					{/foreach}
				{/data_table}
				{submit value='Print'}
			</div>
		{/form}
	</div>
{/content_wrapper}