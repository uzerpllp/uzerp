{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
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
		{form controller=$controller action='printdocumentation'}
			<input type="hidden" name="id" value="{$transaction->id}">
			{view_section heading="Print Documentation"}
                {include file='elements/select_printer.tpl'}
                <dt>Output Type:</dt>
                <dd>
                	<input type="radio" name="type" value="print" checked> Print&nbsp;&nbsp;
                	<input type="radio" name="type" value="view"> View<br>
                </dd>
            	{foreach item=name from=$documentation}
					{if $name->name == ''}
					<dt></dt><dd>No documentation available for this order</dd>
					{break}
					{/if}
					{if $name@first}
					<dt>Available Documents:</dt>
					{else}
					<dt></dt>
					{/if}
					<dd>
						<input type="checkbox" class="checkbox" id="document-{$name->id}" name="doc_selection[]" value="{$name->id}">
						<label for="document-{$name->id}">{$name->name}</label>
					</dd>
				{/foreach}
			{/view_section}
			{submit value='Output'}
		{/form}
		</dl>
	</div>
{/content_wrapper}