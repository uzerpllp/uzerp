{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.3 $ *}
{content_wrapper}
	{form controller="sopackingslips" action="save"}
		{with model=$models.SOPackingSlip legend="Sales Order Packing Slip Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='order_id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='name' class="compulsory" }
			{input type='text'  attribute='tracking_code' class="compulsory" }
			{input type='text'  attribute='courier' class="compulsory" }
			{input type='text'  attribute='courier_service' class="compulsory" }
		{/with}
		{data_table}
			{heading_row}
				{heading_cell field='description'}
					Description
				{/heading_cell}
				{heading_cell field='revised_qty' class='numeric right'}
					Order Qty
				{/heading_cell}
				{heading_cell field='revised_qty' class='numeric right'}
					Available Qty
				{/heading_cell}
				{heading_cell field='contents' class='numeric right'}
					Contains
				{/heading_cell}
			{/heading_row}
			{foreach key=description item=qty from=$lines}
				{assign var=available value=$qty-$packed.$description+$contents.$description}
				{if $available<>0}
					{grid_row}
						<td>
							{$description}
						</td>
						<td align=right>
							{$qty}
						</td>
							<td align=right>
							{$available}
							{input type='hidden' model=$models.SOPackingSlip attribute='available' number=$description value=$available}
						</td>
						<td align=right>
							{input type='text' model=$models.SOPackingSlip attribute='contains' number=$description value=$contents.$description tags=none label='' class='numeric'}
						</td>
					{/grid_row}
				{/if}
			{/foreach}
		{/data_table}
		{submit}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}