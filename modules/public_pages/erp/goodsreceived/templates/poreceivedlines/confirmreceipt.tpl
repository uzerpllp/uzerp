{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.26 $ *}
{content_wrapper}
	{advanced_search}
	{form controller="poreceivedlines" action="confirm_receipt"}
		<dl id="view_data_left">
			<dt>Customer Delivery Note</dt>
			<dd>{input type='text' attribute='delivery_note' label=' ' tags='none'}</dd>
		</dl>
		{data_table}
			{heading_row}
				{heading_cell field='order_number'}
					Order Number
				{/heading_cell}
				{heading_cell field='due_delivery_date'}
					Due Date
				{/heading_cell}
				{heading_cell field='description'}
					Item Description
				{/heading_cell}
				{heading_cell field='stitem'}
					Item Code
				{/heading_cell}
				{heading_cell field='os_qty' class=right}
					Expected
				{/heading_cell}
				{heading_cell field='uom_name' class=right}
					UoM
				{/heading_cell}
				<th class="right">
					Received
				</th>
				<th>
					Receive into
				</th>
				<th>
					Confirm Receipt?
				</th>
			{/heading_row}
			{foreach name=datagrid item=model from=$porderlines}
				{grid_row}
					<td  width=50 align=right>
						{link_to module='purchase_order' controller='porders' action='view' id=$model->order_id value=$model->order_number}
					</td>
					<td>
						{$model->getFormatted('due_delivery_date')}
					</td>
					<td>
						{$model->description}
					</td>
					<td>
						{$model->stitem}
					</td>
					<td>
						{$model->os_qty}
					</td>
					<td>
						{$model->uom_name}
					</td>
					<td align='right'>
						{input class="numeric" model=$model attribute="received_qty" number=$model->id value=$model->os_qty tags=none label=' '}
					</td>
					<td width=10 align=center>
						{$model->to_location}
						{if ($model->stitem_id)!='' && !empty($model->to_bin_list)}
							{select model=$model attribute="to_whbin_id" number=$model->id options=$model->to_bin_list tags=none label=' ' nonone=true}
						{else}
							{input model=$model type='hidden' attribute="to_whbin_id" number=$model->id value=""}
						{/if}
					</td>
					<td width=10 align=center>
						{input type='hidden' attribute="id" number=$model->id}
						{input model=$model type='hidden' attribute="stitem_id" number=$model->id value=$model->stitem_id}
						{input model=$model type='hidden' attribute="whaction_id" number=$model->id value=$model->whaction_id}
						{input model=$model type='hidden' attribute="from_whlocation_id" number=$model->id value=$model->from_location_id}
						{input model=$model type='hidden' attribute="received_by" number=$model->id value=$smarty.const.EGS_USERNAME}
						{input model=$model type='hidden' attribute="to_whlocation_id" number=$model->id value=$model->to_location_id}
						{input model=$model type='hidden' attribute="lastupdated" number=$model->id value=$model->lastupdated }
						{input model=$model class="checkbox" type="checkbox" attribute="confirm" number=$model->id tags='none' label=' '}
					</td>
				{/grid_row}
			{foreachelse}
				<tr>
					<td colspan="0">No matching records found!</td>
				</tr>
			{/foreach}
		{/data_table}
		{paging}
		{submit tags='none'}
		{include file='elements/saveAnother.tpl' value='Save and Confirm Another' tags='none'}
		{submit name='savePrintLabels' value='Save and Print labels' tags='none'}
	{/form}
{/content_wrapper}