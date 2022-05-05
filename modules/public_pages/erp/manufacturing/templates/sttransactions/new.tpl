{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{$sub_title}
	{form controller="sttransactions" action="save"}
		{with model=$models.STTransaction legend="STTransaction Details"}
			{input type='hidden'  attribute='whaction_id' }
			{input type='hidden'  attribute='usercompanyid' }
			<table>
				<tr>
					<td>
						{if count($stock_items)==0 }
							{select label='Stock Item' attribute='stitem_id' }
						{else}
							{if count($stock_items)==1 }
								{input type="hidden" attribute="stitem_id" value="$stitem_id"}
								{$stock_item }
							{else}
								{select attribute='stitem_id'  label='Stock Item' value=$stitem_id}
								<div id="ui-message"</div>
							{/if}
						{/if}
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{select attribute='from_whlocation_id' options=$from_locations value=$from_whlocation_id nonone=true label='From Location'}
					</td>
					<td>
						{select attribute='from_whbin_id' options=$from_bins nonone=true label='Bin'}
					</td>
				</tr>
				<tr>
					<td>
						{select attribute='to_whlocation_id' options=$to_locations nonone=true label='To Location'}
					</td>
					<td>
						{select attribute='to_whbin_id' options=$to_bins nonone=true label='Bin'}
					</td>
				</tr>
				<tr>
					<td>
						{input type='text' attribute='balance' value="$balance" readonly=true label='Available Balance is'}
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{input type='text' attribute='uom_id' value="$uom" readonly=true label='Unit of Measure'}
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{input type='text'  attribute='qty' label=$label }
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{input type='text'  attribute='remarks'}
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{* select  attribute='process_name' label='Type' *}
					</td>
					<td>
					</td>
				</tr>
				<tr>
					<td>
						{* input type='text'  attribute='process_id' label='Reference' *}
					</td>
					<td>
					</td>
				</tr>
			</table>
		{/with}
		{submit}
		{submit value="Save and add another" name="saveAnother"}
	{/form}
	{include file='elements/cancelForm.tpl'}
{/content_wrapper}