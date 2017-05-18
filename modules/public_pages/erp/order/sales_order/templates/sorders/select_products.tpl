{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.10 $ *}
{content_wrapper}
	{advanced_search}
	<div id="view_page" class="clearfix">
		{form controller="sorders" action="saveselectedproducts"}
			<dl id="view_data_left">
				{view_section heading="Customer : $slcustomer"}
					<br>
					<input type='hidden' value={$slmaster_id} name="SOrder[slmaster_id]" id="SOrder_slmaster_id" >
					<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
						<thead>
							<tr>
								<th>Item</th>
								<th>Currency</th>
								<th class="right">Price</th>
								<th>UoM</th>
								<th>Price List</th>
								<th class="right">Available Stock</th>
								<th>Select?</th>
							</tr>
						</thead>
						<tbody>
							{foreach name=datagrid item=model from=$soproductlines}
								<tr>
									<td>
										{$model->description}
									</td>
									<td>
										{$model->currency}
									</td>
									<td class="numeric">
										{$model->getPrice()}
									</td>
									<td>
										{$model->uom_name}
									</td>
									<td>
										{$model->so_price_type}
									</td>
									<td class="numeric">
										{if is_null($model->stitem_id)}
											Not Known
										{else}
											{$stitem->getAvailableBalance($model->stitem_id)}
										{/if}
									</td>
									<td>
										{assign var=id value=$model->id}
										{if isset($selected.$id)}
											<input checked class="checkbox" rel="{$id}" id="checkbox_{$id}" type="checkbox" name="SOrder[{$id}]" />
										{else}
											<input class="checkbox" rel="{$id}" id="checkbox_{$id}" type="checkbox" name="SOrder[{$id}]" />
										{/if}
									</td>
								</tr>
							{foreachelse}
								<tr>
									<td colspan="0">No matching records found!</td>
								</tr>
							{/foreach}
						</tbody>
					</table>
					{paging}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading='Selected Products'}
					<input type="hidden" id="products_text" >
					<div id="view_data_bottom">
						<div id="products">
							{include file="./showproducts.tpl"}
						</div>
					</div>
				{/view_section}
			</dl>
			<div id="view_data_bottom">
				{if $slmaster_id>0}
					{submit value='Save Order'}
				{/if}
			</div>
		{/form}
		<div id="view_data_bottom">
			{include file='elements/cancelForm.tpl' cancel_action='saveselectedproducts'}
		</div>
	</div>
{/content_wrapper}