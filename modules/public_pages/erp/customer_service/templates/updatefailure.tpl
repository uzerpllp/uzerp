{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{content_wrapper}
	{foreach name=datagrid item=cs from=$customerservice}
		{form controller="customerservices" action="savefailure"}
			<div id="view_page" class="clearfix">
				{with model=$cs}
					{input type='hidden' attribute='id'}
					{input type='hidden' attribute='product_group'}
					{input type='hidden' attribute='slmaster_id'}
					{view_data attribute='product_group'}
					{view_data attribute='customer'}
					{view_data attribute='stitem' label='Item'}
					{view_data attribute='order_number' label='Sales Order Number' link_to='"module":"sales_order", "controller":"sorders", "action":"view", "id":"'|cat:$cs->order_id|cat:'"'}
					{view_data attribute='despatch_number' label='Despatch Number' link_to='"module":"despatch", "controller":"sodespatchlines", "action":"view", "id":"'|cat:$cs->id|cat:'"'}
					{view_data attribute='due_despatch_date' label='Due Despatch Date'}
					{view_data attribute='despatch_date' label='Despatch Date'}
					{view_data attribute='order_qty' label='Order Quantity'}
					{view_data attribute='despatch_qty' label='Despatch Quantity'}
					{select attribute='cs_failurecode_id' options=$failurecodes value=$cs->cs_failurecode_id label='Failure Code' nonone=true}
					{textarea label='Note' attribute='cs_failure_note'}
				{/with}
			</div>
			{submit}
		{/form}
		{include file='elements/cancelForm.tpl'}
	{/foreach}
{/content_wrapper}