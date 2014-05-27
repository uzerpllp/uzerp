{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.6 $ *}
{content_wrapper}
	{form controller="sodespatchlines" action="save_despatchnote"}
		<table class="datagrid" id="datagrid1" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Order</th>
					<th>Due Date</th>
					<th>Customer</th>
					<th>Stock Item</th>
					<th align=right>Required</th>
					<th>UoM</th>
					<th align=right>Revised Qty</th>
					<th align=right>Due Despatch Date</th>
					<th align=center>Create Despatch note?</th>
				</tr>
			</thead>
			<tbody>
				{foreach name=datagrid item=model from=$orders}
					<tr>
						<td>
							<a href="?module={$module}&submodule={$submodule}&controller={$controller}&action=view&id={$model.id}">
								{$model.order_number}
							</a>
						</td>
						<td>
							{$model.due_date}
						</td>
						<td>
							{$model.customer}
						</td>
						<td>
							{$model.stitem}
						</td>
						<td width=50 align=right>
							{$model.required}
						</td>
						<td>
							{$model.stuom}
						</td>
						<td align=right>
							<input type="text" align="right" size=10 value="{$model.revised_qty}" name="sodespatchlines_revised_qty[{$model.id}]" class="positive numeric" />
						</td>
						<td align=right>
							<input "type=text" align="right" size=10 value="{$model.due_despatch_date}" name="sodespatchlines_due_despatch_date[{$model.id}]" />
						</td>
						<td align=center>
						{if $model.despatch}
							<input class="checkbox" type="checkbox" name="sodespatchlines_despatch[{$model.id}]" />
						{else}
							Insufficent Stock
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
		{submit}
	{/form}
{/content_wrapper}