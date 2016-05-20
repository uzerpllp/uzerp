{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.16 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{form controller=$controller action="printItemLabels"}
			<dl class="float-left">
				{with model=$SOrder}
					{view_section heading=$SOrder->getFormatted('type')|cat:' Details' expand="open"}
						{view_data attribute="order_number" label=$SOrder->getFormatted('type')|cat:' number'}
						{view_data attribute="customer" label='customer'}
                        {input type='hidden' attribute='id'}
					{/view_section}
				{/with}
			</dl>
			<dl id="view_data_right">
                {include file='elements/select_printer.tpl'}
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
						{heading_cell field="uom_name"}
                            UoM
                        {/heading_cell}
                        {heading_cell field="status"}
                            Status
                        {/heading_cell}
						{heading_cell field="print_qty" class='right'}
							Print Qty
						{/heading_cell}
						{heading_cell field="confirm"}
							Print Labels
						{/heading_cell}
					{/heading_row}
					{foreach name=lines item=line from=$SOrder->lines}
						{assign var=key value=$line->id}
						{if ($line->status==$line->newStatus() || $line->status==$line->partDespatchStatus() || $line->status==$line->pickedStatus()) && $line->not_despatchable=='f'}
							{grid_row data_line=$line->id}
								{input type='hidden' model=$line rowid=$line->id number=$line->id attribute=stitem_id}
								{input type='hidden' model=$line rowid=$line->id number=$line->id attribute=line_number}
								{assign var=pick_qty value=$line->os_qty}
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
								{grid_cell model=$line cell_num=2 field="os_qty" no_escape=true}
									{$line->os_qty}
									{input type="hidden" model=$line attribute="os_qty" rowid=$key number=$key}
								{/grid_cell}
								{grid_cell model=$line cell_num=2 field="uom_name"}
                                    {$line->uom_name}
                                {/grid_cell}
								<td align=left>
								    {if ($line->status==$line->pickedStatus())}
								        Picked
								    {/if}
								</td>
								<td class="numeric">
									{input type="text" model=$line attribute="print_qty" rowid=$key number=$key value=$pick_qty class="numeric print_qty" tags=none label=" "}
								</td>
									<td align=left data-line_status="{$line->status}">
										{input type="checkbox" class="checkbox" model=$line attribute="id" rowid=$key number=$key tags=none label=' '}
									</td>
							{/grid_row}
						{/if}
					{/foreach}
					{grid_row}
						<td colspan=7 align='right'>
							<input type='button' value="Clear/Select All" class="select_all"> <input type='button' value="Select Picked" class="select_picked">
						</td>
					{/grid_row}
				{/data_table}
				{submit value='Print'}
			</div>
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl'}
		</div>
	</div>
	<div id="print-confirm" title="Print labels" style="display:none;">
    <p style="text-align:center;"><strong>Print labels to [printer] printer?</strong></p>
    </div>
    <div id="selection-warning" title="Hint" style="display:none;">
    <p style="text-align:center;"><strong>Please select at least one line for printing</strong></p>
    </div>
</div>
{/content_wrapper}