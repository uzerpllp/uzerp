{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* 	$Revision: 1.5 $ *}
<div id="sales_order-sorders-showproducts">
	{with model=$productline}
		<table class="datagrid" id="datagrid2" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th class="wide_column">
						Description
					</th>
					<th>
						Currency
					</th>
					<th class='right'>
						Price
					</th>
					<th>
						UoM
					</th>
					<th>
						Quantity
					</th>
					<th width=10px>
					</th>
				</tr>
			</thead>
			<tbody style="height: auto; width:auto;">
				{foreach item=productline from=$productlines}
					<tr>
						<td>
							{$productline->description}
						</td>
						<td>
							{$productline->currency}
						</td>
						<td align='right'>
							{$productline->getPrice()}
						</td>
						<td>
							{$productline->uom_name}
						</td>
						<td>
							{input type='text' attribute='qty' tags=none nolabel=true value=1 class='numeric' number=$productline->id rowid=$productline->id}
						</td>
						<td width=10px>
							<button class="remove" rel="{$productline->id}"><img alt="remove" src='{$smarty.const.THEME_URL}{$theme}/graphics/cancel.png'"/></button>
						</td>
					</tr>
				{foreachelse}
					<tr>
						<td colspan=2>
							None Selected
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{/with}
</div>