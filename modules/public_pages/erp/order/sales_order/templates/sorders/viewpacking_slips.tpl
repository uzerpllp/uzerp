{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.8 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{view_section heading=$SOrder->getFormatted('type')|cat:' Header' expand='open'}
			<dl class="float-left">
				{with model=$SOrder}
					{view_section heading=$SOrder->getFormatted('type')|cat:' Details' expand='closed'}
						{view_data attribute="order_number" label=$SOrder->getFormatted('type')|cat:' number'}
						{view_data attribute="customer" label='customer'}
						{view_data attribute="ext_reference" label='customer_reference'}
						{view_data attribute="status"}
					{/view_section}
					{view_section heading="Timescale" expand='closed'}
						{view_data attribute="order_date" label=$SOrder->getFormatted('type')|cat:' date'}
						{view_data attribute="despatch_date"}
						{view_data attribute="due_date"}
						{view_data value=','|implode:$SOrder->despatch_from->rules_list('from_location') label='Despatch From'}
					{/view_section}
				{/with}
			</dl>
			<dl class="float-right">
				{view_section heading="delivery_address" expand='closed'}
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
					{view_section heading="further_details" expand='closed'}
						{view_data attribute="currency"}
						{view_data attribute="net_value"}
						{view_data attribute="base_net_value"}
					{/view_section}
					{view_section heading="Order Line Summary" expand='closed'}
						{foreach key=status item=value from=$linevalue}
							{if $value>0}
								{view_data attribute=$status value=$value|string_format:"%.2f"}
							{/if}
						{/foreach}
					{/view_section}
				{/with}
			</dl>
		{/view_section}
		<div id="view_data_bottom">
			{form controller="sopackingslips" action="printDialog" class="ignore_rules" notags=true}
				{input type='hidden' name='printaction' value='print_packing_slips'}
				{input type='hidden' model=$SOrder attribute=id}
				{data_table}
					{heading_row}
						{heading_cell field='name'}
							Name
						{/heading_cell}
						{heading_cell field='tracking_code'}
							Tracking Code
						{/heading_cell}
						{heading_cell field='courier'}
							Courier
						{/heading_cell}
						{heading_cell field='courier_service'}
							Courier Service
						{/heading_cell}
						{heading_cell field='contents'}
							Contents
						{/heading_cell}
						{heading_cell field='print'}
							Select
						{/heading_cell}
					{/heading_row}
					{foreach name=lines item=line from=$sopackingslips}
						{grid_row}
							{grid_cell model=$line cell_num=1 field='name' _order_id=$SOrder->id}
								{$line->name}
							{/grid_cell}
							{grid_cell model=$line cell_num=2 field='tracking_code'}
								{$line->tracking_code}
							{/grid_cell}
							{grid_cell model=$line cell_num=3 field='courier'}
								{$line->courier}
							{/grid_cell}
							{grid_cell model=$line cell_num=4 field='courier_service'}
								{$line->courier_service}
							{/grid_cell}
							{grid_cell model=$line cell_num=5 field='contents'}
								{assign var=contents value=$line->contents|base64_decode|unserialize}
								{foreach key=description item=qty from=$contents}
									{if $qty>0}
										{$qty} {$description}
									{/if}
								{/foreach}
							{/grid_cell}
							<td>
								{input type='checkbox' model=$line class='checkbox' attribute='print' number=$line->id label='' tags=none}
							</td>
						{/grid_row}
					{/foreach}
				{/data_table}
				{submit value='Output'}
			{/form}
		</div>
	</div>
{/content_wrapper}