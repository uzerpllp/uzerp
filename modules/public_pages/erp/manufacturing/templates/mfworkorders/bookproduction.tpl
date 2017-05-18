{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{form controller="mfworkorders" action="updatewip"}
		{with model=$transaction legend="MFWorkorder Details"}
			{input type='hidden'  attribute='id' }
			{input type='hidden'  attribute='usercompanyid' }
			{input type='hidden'  attribute='whaction_id' value=$whaction_id}
			{view_data attribute='wo_number' }
			{view_data label='Item Code' value=$item_code }
			{view_data attribute='order_qty' }
			{view_data attribute='required_by' }
			{view_data attribute='made_qty' }
			{input type='text'  attribute='book_qty' label="Book Qty" }
			{input type='text'  attribute='remarks' label="Remarks" }
			{if count($from_locations)==1 }
				{input type="hidden" attribute="from_whlocation_id" value=$from_whlocation_id}
				{view_data attribute='from_whlocation_id' value=$from_whlocation label='From Location'}
			{else}
				{select attribute="from_whlocation_id" options=$from_locations value=$from_whlocation_id label='From Location'}
			{/if}
			{if count($from_bins)==0 }
				{input type="hidden" attribute="from_whbin_id" value=""}
			{else}
				{select attribute="from_whbin_id" options=$from_bins value=$from_whbin_id label='From Bin'}
			{/if}
			{if count($to_locations)==1 }
				{input type="hidden" attribute="to_whlocation_id" value=$to_whlocation_id}
				{view_data attribute='to_whlocation_id' value=$to_whlocation label='To Location'}
			{else}
				{select attribute="to_whlocation_id" options=$to_locations value=$to_whlocation_id label='To Location'}
			{/if}
			{if count($to_bins)==0 }
				{input type="hidden" attribute="to_whbin_id" value=""}
			{else}
				{select attribute="to_whbin_id" options=$to_bins value=$to_whbin_id label='To Bin'}
			{/if}
			{select label='Stock Item' attribute='stitem_id' }
		{/with}
		{submit}
	{/form}
	{include file="elements/cancelForm.tpl"}
{/content_wrapper}