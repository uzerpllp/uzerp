{** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **}
{* $Revision: 1.29 $ *}
{content_wrapper class="clearfix uz-grid"}
	{with model=$models.PInvoice legend="PInvoice Details"}
		<div id="view_page" class="clearfix">
			{form controller="pinvoices" action="save" notags=true}
			    <dl class="float-left" >
					{view_section heading="Details"}
						{input type='hidden'  attribute='id'}
						{include file='elements/auditfields.tpl' }
						{if $action == 'edit'}
							{view_data attribute="invoice_number" label=$PInvoice->getFormatted('transaction_type')|cat:' number'}
							{view_data attribute="net_value" label='invoice_value'}
						{/if}
						{input type='date' label="$transaction_type_desc Date" attribute='invoice_date'}
						{select attribute='transaction_type'}
						{if $model->net_value==0}
							{select attribute="plmaster_id" label='Supplier' force=true value=$selected_supplier use_collection=true}
						{else}
							{input type='hidden' attribute="plmaster_id"}
							{view_data attribute="supplier" label='Supplier'}
						{/if}
						{input type='text' attribute='settlement_discount'}
						{input type='text' attribute='our_reference'}
						{input type='text' attribute='ext_reference' label='Supplier Ref'}
					{/view_section}
					{submit}
				</dl>
			    <dl class="float-right" >
					{view_section heading="Description"}
						{textarea  attribute='description' tags=none label=' '}
					{/view_section}						
					{view_section heading="Project Details"}					
						{select attribute='project_id' force=true}
						{select attribute='task_id' options=$tasks force=true}
					{/view_section}
				</dl>
			{/form}
			<dl class="view_data_bottom">
				{include file="elements/cancelForm.tpl"}
				{view_section heading="Notes"}
					<dt><label for="notes"></label></dt>
					<dd class="inline" id="notes">
						{include file="elements/datatable_inline.tpl"}
					</dd>
				{/view_section}
			</dl>
		</div>
	{/with}
{/content_wrapper}