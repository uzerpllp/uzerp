{** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.13 $ *}
{content_wrapper}
	<div id="view_page" class="clearfix">
		<dl id="view_data_left">
			{with model=$transaction}
				<dt class="heading">General</dt>
				{view_data attribute='wo_number' label='Order No.'}
				{view_data attribute='stitem_id' label='Stock Item'}
	 			{view_data attribute='data_sheet'}
	 			{view_data attribute='order_qty'}
				{view_data attribute='made_qty'}
				{view_data attribute='created' label='Date Raised'}
				{view_data attribute='required_by'}
				{view_data attribute='project'}
				{view_data attribute='text1'}
				{view_data attribute='text2'}
				{view_data attribute='text3'}
				{view_data attribute='status'}
				{view_data attribute='order_id' label='Sales Order'}
				{view_data attribute='orderline_id' label='Sales Order Line'}
			{/with}
		</dl>
		<dl id="view_data_left">
			{view_section heading="Documentation"}
				{foreach item=name from=$documentation}
					<dt></dt>
					<dd>
						{link_to module='manufacturing' controller='mfworkorders' action='printSingleAction' printaction='printSingleReport' id=$transaction->id report=$name->class_name value=$name->name}
					</dd>
				{/foreach}
			{/view_section}
		</dl>
	</div>
{/content_wrapper}