{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$POrder}
				<dt class="heading">Goods Received Note {$grn->gr_number}</dt>
				{view_data value=$grn->received_date label="received_date"}
				{view_data value=$grn->delivery_note label="delivery_note"}
				{view_data attribute="order_number" label=$POrder->getFormatted('type')|cat:' number'
				link_to='"module":"purchase_order","controller":"porders","action":"view","id":"'|cat:$POrder->id|cat:'"'}
				{view_data attribute="supplier" label='supplier'}
				{view_data attribute="ext_reference" label='supplier_reference'}
				{view_data attribute="status" value=$POrder->getFormatted('status') label="Order Status"}
				{view_data attribute="order_date" label=$POrder->getFormatted('type')|cat:' date'}
				{view_data attribute="due_date"}
			{/with}
		</dl>
		<div id="view_data_bottom">
			<dt class="heading">Goods Received Details</dt>
			{data_table}
				{heading_row}
					{heading_cell field="received_qty" class='right'}
						Delivered
					{/heading_cell}
					{heading_cell field="uom_name"}
						UoM
					{/heading_cell}
					{heading_cell field="item_description"}
						Item
					{/heading_cell}
					{heading_cell field="description"}
						Description
					{/heading_cell}
					{heading_cell field="status"}
						Status
					{/heading_cell}
				{/heading_row}
				{foreach name=lines item=line from=$POReceivedlines}
					{grid_row model=$line}
						{grid_cell model=$line cell_num=2 field="received_qty" class='numeric'}
							{$line->received_qty}
						{/grid_cell}
						{grid_cell model=$line cell_num=2 field="uom_name"}
							{$line->uom_name}
						{/grid_cell}
						{grid_cell model=$line cell_num=2 field="item_description"}
							{$line->item_description}
						{/grid_cell}
						{grid_cell model=$line cell_num=2 field="description"}
							{$line->order_line->description}
						{/grid_cell}
						{grid_cell model=$line cell_num=2 field="status"}
							{$line->getFormatted('status')}
						{/grid_cell}
					{/grid_row}
				{/foreach}
			{/data_table}
		</div>
	</div>
{/content_wrapper}