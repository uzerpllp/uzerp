{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.5 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		{with model=$Asset}
			<dl id="view_data_left">
				{view_section heading="general"}
					{view_data attribute="code"}
					{view_data attribute="serial_no"}
					{view_data attribute="argroup" label='Asset Group' link_to='"controller":"argroups", "module":"asset_register", "action":"view", "id":"'|cat:$Asset->argroup_id|cat:'"'}
					{view_data attribute="arlocation" label='Asset Location' link_to='"controller":"arlocations", "module":"asset_register", "action":"view", "id":"'|cat:$Asset->arlocation_id|cat:'"'}
					{view_data attribute="aranalysis" label='Asset Analysis'}
					{view_data attribute="supplier"}
					{view_data attribute="description"}
				{/view_section}
			</dl>
			<dl id="view_data_right">
				{view_section heading="acquired"}
					{view_data attribute="purchase_date"}
					{view_data attribute="purchase_price"}
				{/view_section}
				{view_section heading="movements"}
					{view_data attribute="bfwd_value" label='Brought forward value'}
					{view_data attribute="ty_depn" label='Depreciation this year'}
					{view_data attribute="td_depn" label='Depreciation to date'}
					{view_data attribute="wd_value" label='Written Down Value'}
				{/view_section}
				{view_section heading="disposed"}
					{view_data attribute="residual_value"}
					{view_data attribute="disposal_date"}
					{view_data attribute="disposal_value"}
				{/view_section}
			</dl>
		{/with}
	</div>
{/content_wrapper}