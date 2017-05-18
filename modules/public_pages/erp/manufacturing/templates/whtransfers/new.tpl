{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.8 $ *}
{content_wrapper}
	<script type="text/javascript">
		var count={$nextval|default:0};
	</script>
	<div id="{page_identifier}" class="clearfix uz-grid">
		{form controller="whtransfers" action="save" notags=true}
			{with model=$models.WHTransfer legend="WHTransfer Details"}
			<input type="hidden" id="WHTransfer_status" name="WHTransfer[status]" value="N" />
			{include file='elements/auditfields.tpl' }
			<dl id="view_data_left">
				{if $model->transfer_number<>''}
					<dt>Transfer Number</dt>
					<dd>{input type='text'  attribute='transfer_number' label=' ' tags='none' readonly=true}</dd>
				{/if}
				<dt>Due Transfer Date</dt>
				<dd>{input type='date'  attribute='due_transfer_date' label=' ' tags='none'}</dd>
			</dl>
			<dl id="view_data_left">
				<dd>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</dd>
			</dl>
			<dl id="view_data_left">
				<dd>{input type='hidden'  attribute='id' }</dd>
				<dt><label for="transfer_action">Transfer Type</label>:</dt><dd>
					<select name="WHTransfer[transfer_action]" id="WHTransfer_transfer_action" >
						{html_options options=$transfer_actions selected=$model->transfer_action}
					</select>
				</dd>
				<dt><label for="from_whlocation">From</label>:</dt><dd>
					<select name="WHTransfer[from_whlocation_id]" id="WHTransfer_from_whlocation" >
						{html_options options=$from_locations selected=$model->from_whlocation_id}
					</select>
				</dd>
				<dt><label for="to_whlocation">To</label>:</dt><dd>
					<select name="WHTransfer[to_whlocation_id]" id="WHTransfer_to_whlocation" >
						{html_options options=$to_locations selected=$model->to_whlocation_id}
					</select>
				</dd>
			</dl>
			<dl id="view_data_left">
				<dd>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</dd>
			</dl>
			<dl id="view_data_left">
				{textarea  attribute='description' label='Description' }
			</dl>
			{/with}
		<dl id="view_data_bottom"><dd>
		<div id="whtransfer_container" class="grid_form_container">
		<br /><b>Transfer Lines</b>
		<table id="gridform" class="uz-grid-table" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th>Item</th>
					<th>Available</th>
					<th>Quantity</th>
					<th>UoM</th>
					<th>Remarks</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tfoot>
			<tr>
				<td></td>
			</tr>
			<tr>
				<td colspan="8" align="right"><br /><input type="button" class="uz-grid-add-row" value="Add Line" id="addrowbutton"/></td>
			</tr>
			<tr>
				<td colspan="8" align="right"><br />{submit tags='none' id='grid_submit'}</td>
			</tr>
			<tr>
				<td colspan="8" align="right"><br />{submit tags='none' id='grid_cancel' value='Cancel'}</td>
			</tr>
			</tfoot>
			<tbody>
			{if $action == 'edit'}
				{assign var=count value=1}
				{assign var=total_value value=0}
				{foreach name=datagrid item=submodel from=$whtransfer->transfer_lines}
					{with model=$models.WHTransferline legend="WHTransferline Details"}
						{assign var=rowid value='row'|cat:$count}
						<tr class="gridrow" id="{$rowid}" >
							<td align="center">
								<input type="hidden" name="WHTransferLine[id][{$count}]" value="{$submodel->id}">
								<select id="stitems{$rowid}" data-field="stitem_id" name="WHTransferLine[{$count}][stitem_id]" class="copy_value">
									{html_options options=$stitems selected=$submodel->stitem_id}
								</select>
							</td>
							<td align="center">
								<input type="text" data-field="available_qty" name="WHTransferLine[{$count}][available_qty]" value="0" id="available_qty{$rowid}" class="available_qty numeric" readonly />
							</td>
							<td align="center">
								<input type="text" data-field="transfer_qty" name="WHTransferLine[{$count}][transfer_qty]" value="{$submodel->transfer_qty}" id="transfer_qty{$rowid}" class="transfer_qty numeric" />
							</td>
							<td align="center">
								<input type="hidden" id="stuom_id{$rowid}" name="WHTransferLine[{$count}][stuom_id]" value="{$submodel->stuom_id}" readonly />
								<input type="text" data-field="uom_name" id="uom_name{$rowid}" name="WHTransferLine[{$count}][uom_name]" value="{$submodel->uom_name}" readonly />
							</td>
							<td align="center">
								<input type="text" data-field="remarks" name="WHTransferLine[{$count}][remarks]" id="remarks{$rowid}" size="50" value="{$submodel->remarks}" />
							</td>
							<td align="center">
								<a class="uz-grid-remove-row" href="JavaScript:void(0);"><img src="/assets/graphics/delete.png" /></a>
							</td>
						</tr>
						<script type="text/javascript">
							changeTextValue('stitemsrow{$count}', 'available_qtyrow{$count}', Array('module=manufacturing', 'controller=STItems', 'action=getStockBalanceAtLocation', 'whlocation_id={$whtransfer->from_whlocation_id}'));
						</script>
						{assign var=count value=$count+1}
					{/with}
				{/foreach}
			{/if}
			</tbody>
		</table>
		</div>
		</dd></dl>
		{/form}
		<form>
		<table>
			{with model=$models.WHTransferLine legend="WHTransferLine Details"}
				<tr class="gridrow uz-grid-hidden-row" id="rowtemplate" style="display:none;">
					<td align="center">
						<select id="stitems" data-field="stitem_id" name="WHTransferLine[_REPLACE_][stitem_id]" class="copy_value">
							{html_options options=$stitems}
						</select>
					</td>
					<td align="center">
						<input type="text" data-field="available_qty" name="WHTransferLine[_REPLACE_][available_qty]" value="0" id="available_qty" class="available_qty numeric" readonly />
					</td>
					<td align="center">
						<input type="text" data-field="transfer_qty" name="WHTransferLine[_REPLACE_][transfer_qty]" value="0" id="transfer_qty" class="transfer_qty numeric" />
					</td>
					<td align="center">
						<input type="hidden" id="stuom_id" name="WHTransferLine[_REPLACE_][stuom_id]" readonly />
						<input type="text" data-field="uom_name" id="uom_name" name="WHTransferLine[_REPLACE_][uom_name]" readonly />
					</td>
					<td align="center">
						<input type="text" data-field="remarks" name="WHTransferLine[_REPLACE_][remarks]" id="remarks" size="50" />
					</td>
					<td align="center">
						<a class="uz-grid-remove-row" href="JavaScript:void(0);"><img src="/assets/graphics/delete.png" /></a>
					</td>
				</tr>
			{/with}
		</table>
		</form>
	</div>
	<script type="text/javascript">
		$(document).ready(function() {
			legacyForceChange('#WHTransfer_transfer_action');
		});
	 </script>
{/content_wrapper}
