{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller=$controller action="save_unpick_list"}
			<dl class="float-left">
				{with model=$SOrder}
					{view_section heading=$SOrder->getFormatted('type')|cat:' Details' expand="open"}
						{view_data attribute="order_number" label=$SOrder->getFormatted('type')|cat:' number'}
						{view_data attribute="customer" label='customer'}
						{select attribute="from_location_id" options=$from_locations label='Return From' nonone=true}
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
						{heading_cell field="order_qty" class='right'}
							Order Qty
						{/heading_cell}
						{heading_cell field="del_qty" class='right'}
							Un Pick Qty
						{/heading_cell}
						{heading_cell field="uom_name"}
							UoM
						{/heading_cell}
						{heading_cell field="from"}
							Return To
						{/heading_cell}
						{heading_cell field="confirm"}
							Confirm
						{/heading_cell}
					{/heading_row}
					{foreach name=lines item=line from=$SOrder->lines}
						{assign var=key value=$line->id}
						{if $line->status==$line->pickedStatus()}
							{grid_row}
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
								{grid_cell model=$line cell_num=2 field="revised_qty"}
									{$line->revised_qty}
								{/grid_cell}
								<td class='numeric'>
									{input model=$line attribute="del_qty" number=$key value=$line->revised_qty class='numeric del_qty' tags=none label=' '}
								</td>
								{grid_cell model=$line cell_num=2 field="uom_name"}
									{$line->uom_name}
								{/grid_cell}
								{if empty($action_list.$key)}
									<td>Non Stock Item</td>
								{else}
									<td>
										{select model=$line attribute="whlocation_id" number=$key options=$action_list.$key nonone=true tags=none label=' '}
									</td>
								{/if}
								<td align=center>
									{input type="checkbox" class="checkbox" model=$line attribute="id" number=$key tags=none label=' '}
								</td>
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
		{include file='elements/cancelForm.tpl'}
	</div>
{/content_wrapper}