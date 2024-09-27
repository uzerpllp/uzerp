{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.4 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{view_data model=$transaction label='Date Created' attribute="created"}
			{if $transaction->qty < 0 or $transaction->error_qty < 0}
				{view_data model=$transaction label='From Location' attribute="whlocation" link_to='"module":"manufacturing","controller":"whlocations","action":"view","id":"'|cat:$transaction->whlocation_id|cat:'"'}
				{if !empty($transaction->whbin_id)}
					{view_data model=$transaction label='From Bin' attribute="whbin" link_to='"module":"manufacturing","controller":"whbins","action":"view","id":"'|cat:$transaction->whbin_id|cat:'"'}
				{/if}
			{else}
				{view_data model=$transaction label='To Location' attribute="whlocation" link_to='"module":"manufacturing","controller":"whlocations","action":"view","id":"'|cat:$transaction->whlocation_id|cat:'"'}
				{if !empty($transaction->whbin_id)}
					{view_data model=$transaction label='To Bin' attribute="whbin" link_to='"module":"manufacturing","controller":"whbins","action":"view","id":"'|cat:$transaction->whbin_id|cat:'"'}
				{/if}
			{/if}
			{view_data model=$transaction label='Stock Item' attribute="stitem" link_to='"module":"manufacturing","controller":"stitems","action":"view","id":"'|cat:$transaction->stitem_id|cat:'"'}
			{view_data model=$transaction label='Quantity Moved' attribute="positive_qty()"}
			{view_data model=$transaction label='Quantity Yet To Move' attribute="positive_error_qty()"}
			{view_data model=$transaction label='Balance at create date' attribute="balance"}
			{view_data model=$transaction attribute="current_balance"}
			{if $transaction->status == 'E'}
				{view_data model=$transaction label='Balance If Resolved' attribute="resolved_balance()"}
			{/if}
			{view_data model=$transaction attribute="status"}
			{view_data model=$transaction attribute="remarks"}
			{view_data model=$transaction attribute="process_action" link_to='"module":"manufacturing_setup","controller":"whactions","action":"view","id":"'|cat:$transaction->whaction_id|cat:'"'}
			{view_data model=$transaction attribute="process_name" link_to=$linkto}
			{view_data model=$transaction attribute="createdby"}
			{view_data model=$transaction attribute="created"}
		</dl>
	</div>
{/content_wrapper}