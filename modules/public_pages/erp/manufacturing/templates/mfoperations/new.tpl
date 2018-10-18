{**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **}
{* $Revision: 1.7 $ *}
{content_wrapper}
	{form controller="mfoperations" action="save"}
	<div id="view_page" class="clearfix">
		<dl class="float-left" >
		{with model=$models.MFOperation legend="MFOperation Details"}
			{input type='hidden'  attribute='id' }
			{include file='elements/auditfields.tpl' }
			{input type='text'  attribute='op_no' class="compulsory" }
			{select attribute='type'}
			{input type='date'  attribute='start_date' class="compulsory" }
			{input type='date'  attribute='end_date' class="compulsory" }
			
			<div class="all-type" {if $model->type != 'O'}style="display: block;"{else}style="display: none;"{/if}>
				{select label='Stock Item' attribute='stitem_id' options=$stitems}
				{select label='Work Centre' attribute='mfcentre_id' options=$mfcentres}
				{if $stitem->cost_basis == 'VOLUME'}
					{select attribute=volume_uom_id options=$uom_list selected=$model->volume_uom_id}
					{input type='text'  attribute='volume_target' }
					{if $action !== 'edit'}
					{select label='Per' attribute='volume_period' value=$module_prefs['default-operation-units']}
					{else}
					{select label='Per' attribute='volume_period'}
					{/if}
					{input type='text'  label="Quality Target(%)" attribute='quality_target' }
					{input type='text'  label="Uptime Target(%)" attribute='uptime_target' }
				{else}
					{select attribute=volume_uom_id options=$uom_list selected=$model->volume_uom_id label='UOM'}
					{input type='text'  attribute='volume_target' label='Time' class='compulsory' }
					{if $action !== 'edit'}
					{select attribute='volume_period' label='Time Units' value=$module_prefs['default-operation-units']}
					{else}
					{select attribute='volume_period' label='Time Units'}
					{/if}
				{/if}
				{select label='Resource' attribute='mfresource_id' options=$mfresources}
				{input type='text'  attribute='resource_qty' }
			</div>
			
			<div class="o-type" {if $model->type == 'O'}style="display: block;"{else}style="display: none;"{/if}>
				{input type='text' attribute='lead_time' label='Outside Processing Lead Time'}
				{input type='text' attribute='outside_processing_cost'}
				{select attribute='po_productline_header_id' label='Outside Processing Purchase'}
			</div>

		</dl>
		<dl class="float-right">
			{input type='text'  attribute='remarks' placeholder='Short description'}
			{textarea attribute='description' rows='30' cols='40'}
		{/with}
			<dt class="submit"></dt>
			<dd class="submit form-buttons-inline">
				<input class="formsubmit uz-validate" value="Save" name="saveform" id="saveform" type="submit">
				<input class="formsubmit uz-validate" value="Save and Add Another" name="saveadd" id="saveadd" type="submit">
				<a href="{$cancel_link}">Cancel</a>
			</dd>
		</dl>
	</div>
	{/form}


	<div id='show_parts'>
		{include file='./show_parts.tpl'}
	</div>
{/content_wrapper}